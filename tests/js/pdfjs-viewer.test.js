'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const { pathToFileURL } = require('node:url');

const root = path.resolve(__dirname, '..', '..');
const lib = path.join(root, 'assets/lib/pdfjs');
const viewerDir = path.join(root, 'assets/pdf-viewer');
const readLib = (file) => fs.readFileSync(path.join(lib, file), 'utf8');
const readViewer = (file) => fs.readFileSync(path.join(viewerDir, file), 'utf8');

const library = readLib('build/pdf.mjs');
const viewer = readViewer('viewer.mjs');
const page = readViewer('viewer.html');

// First release fixed for CVE-2024-4367, the glyph-path injection through the font matrix.
const FIRST_FIXED = [4, 2, 67];
const minimum = (version) => {
  const parts = version.split('.').map(Number);
  for (let i = 0; i < FIRST_FIXED.length; i += 1) {
    if (parts[i] !== FIRST_FIXED[i]) {
      return parts[i] > FIRST_FIXED[i];
    }
  }
  return true;
};

test('the built pdf.js is at or after the first release fixed for CVE-2024-4367', () => {
  const header = library.match(/pdfjsVersion = (\d+\.\d+\.\d+)/);
  assert.ok(header, 'build/pdf.mjs states its version in the header');
  assert.ok(minimum(header[1]), `pdf.js ${header[1]} is older than ${FIRST_FIXED.join('.')}`);
});

test('the library, the worker and the viewer components agree on one version', () => {
  const header = library.match(/pdfjsVersion = (\d+\.\d+\.\d+)/)[1];
  const escaped = header.replaceAll('.', '\\.');
  assert.match(library, new RegExp(`const version = "${escaped}";`));
  assert.match(readLib('build/pdf.worker.mjs'), new RegExp(`pdfjsVersion = ${escaped}`));
  assert.match(readLib('web/pdf_viewer.mjs'), new RegExp(`pdfjsVersion = ${escaped}`));
});

test('the built pdf.js matches the pdfjs-dist version package.json lists', () => {
  const header = library.match(/pdfjsVersion = (\d+\.\d+\.\d+)/)[1];
  const listed = JSON.parse(fs.readFileSync(path.join(root, 'package.json'), 'utf8')).dependencies?.['pdfjs-dist'];
  assert.ok(listed, 'package.json lists pdfjs-dist as a dependency');
  const lock = JSON.parse(fs.readFileSync(path.join(root, 'package-lock.json'), 'utf8'));
  assert.equal(lock.packages['node_modules/pdfjs-dist'].version, header, 'the locked pdfjs-dist is the version built into assets/lib/pdfjs');
});

test('no built script compiles glyph paths with the Function constructor', () => {
  for (const file of ['build/pdf.mjs', 'build/pdf.worker.mjs', 'web/pdf_viewer.mjs']) {
    assert.doesNotMatch(readLib(file), /new Function\s*\(|isEvalSupported/, `${file} builds code from strings`);
  }
});

test('the release archive viewer is gone and the first-party page is the only viewer', () => {
  for (const file of ['web/viewer.html', 'web/viewer.mjs', 'web/viewer.css']) {
    assert.equal(fs.existsSync(path.join(lib, file)), false, `${file} should not be vendored`);
  }
  assert.match(page, /<script src="viewer\.mjs" type="module"><\/script>/);
  assert.match(page, /<link rel="stylesheet" href="\.\.\/lib\/pdfjs\/web\/pdf_viewer\.css">/);
});

test('the viewer imports only files the build writes', () => {
  const imports = [...viewer.matchAll(/from '(\.[^']+)'/g)].map((match) => match[1]);
  assert.ok(imports.length >= 3, 'the viewer imports the library, the viewer components and the origin check');
  for (const specifier of imports) {
    assert.ok(fs.existsSync(path.resolve(viewerDir, specifier)), `${specifier} exists`);
  }
  assert.ok(fs.existsSync(path.join(lib, 'build/pdf.worker.mjs')), 'the worker the viewer starts is built');
});

async function originCheck() {
  return (await import(pathToFileURL(path.join(viewerDir, 'file-origin.mjs')).href)).assertSameOrigin;
}

const VIEWER = 'https://example.test/wp-content/plugins/x/assets/pdf-viewer/viewer.html?file=a';

test('the origin check accepts a file from the viewer origin', async () => {
  const assertSameOrigin = await originCheck();
  assert.equal(assertSameOrigin('https://example.test/wp-content/uploads/report.pdf', VIEWER), 'https://example.test/wp-content/uploads/report.pdf');
  assert.equal(assertSameOrigin('/wp-content/uploads/report.pdf?v=2#page=3', VIEWER), 'https://example.test/wp-content/uploads/report.pdf?v=2');
});

test('the origin check refuses a file on another origin', async () => {
  const assertSameOrigin = await originCheck();
  for (const file of [
    'https://elsewhere.test/report.pdf',
    'http://example.test/report.pdf',
    'https://example.test:8443/report.pdf',
    '//elsewhere.test/report.pdf',
    'data:application/pdf;base64,AAAA',
    'javascript:alert(1)',
  ]) {
    assert.throws(() => assertSameOrigin(file, VIEWER), /file origin does not match viewer's/, file);
  }
});

test('the viewer runs the origin check before it opens the file', () => {
  const check = viewer.indexOf('assertSameOrigin(requested, window.location.href)');
  const open = viewer.indexOf('pdfjs.getDocument(');
  assert.ok(check > -1, 'the viewer calls assertSameOrigin on the file query value');
  assert.ok(open > -1, 'the viewer opens the document');
  assert.ok(check < open, 'the check comes before the document is opened');
  assert.match(viewer.slice(check, open), /return;/, 'a refused file stops the viewer');
});
