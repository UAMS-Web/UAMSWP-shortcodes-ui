'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..', '..');
const dir = path.join(root, 'assets/lib/pdfjs');
const read = (file) => fs.readFileSync(path.join(dir, file), 'utf8');

const library = read('build/pdf.mjs');
const viewer = read('web/viewer.mjs');
const page = read('web/viewer.html');

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

test('the vendored release is at or after the first one fixed for CVE-2024-4367', () => {
  const header = library.match(/pdfjsVersion = (\d+\.\d+\.\d+)/);
  assert.ok(header, 'build/pdf.mjs states its version in the header');
  assert.ok(minimum(header[1]), `pdf.js ${header[1]} is older than ${FIRST_FIXED.join('.')}`);
});

test('the library, the worker and the viewer agree on one version', () => {
  const header = library.match(/pdfjsVersion = (\d+\.\d+\.\d+)/)[1];
  assert.match(library, new RegExp(`const version = "${header.replaceAll('.', '\\.')}";`));
  assert.match(read('build/pdf.worker.mjs'), new RegExp(`pdfjsVersion = ${header.replaceAll('.', '\\.')}`));
  assert.match(viewer, new RegExp(`pdfjsVersion = ${header.replaceAll('.', '\\.')}`));
});

test('no vendored script compiles glyph paths with the Function constructor', () => {
  for (const file of ['build/pdf.mjs', 'build/pdf.worker.mjs', 'build/pdf.sandbox.mjs', 'build/pdf.renderer.mjs', 'web/viewer.mjs']) {
    assert.doesNotMatch(read(file), /new Function\s*\(|isEvalSupported/, `${file} builds code from strings`);
  }
});

test('the viewer page loads the vendored build and viewer modules', () => {
  assert.match(page, /<script src="\.\.\/build\/pdf\.mjs" type="module"><\/script>/);
  assert.match(page, /<script src="viewer\.mjs" type="module"><\/script>/);
  assert.ok(fs.existsSync(path.join(dir, 'build/pdf.worker.mjs')), 'the worker the viewer starts is vendored');
});

/**
 * Pull `validateFileURL` and the hosted-origins list it reads out of the viewer and run it for a page at `location`.
 */
function validator(location) {
  const start = viewer.indexOf('const HOSTED_VIEWER_ORIGINS = ');
  const end = viewer.indexOf('var onFileInputChange', start);
  assert.ok(start > -1 && end > start, 'validateFileURL is in the viewer');
  const errors = [];
  const sandbox = {
    URL,
    window: { location },
    PDFViewerApplication: { _documentError: (key, details) => errors.push([key, details.message]) },
    Error,
  };
  vm.createContext(sandbox);
  vm.runInContext(`${viewer.slice(start, end)}; this.validateFileURL = validateFileURL;`, sandbox);
  return { validate: sandbox.validateFileURL, errors };
}

test('the viewer accepts a file from its own origin', () => {
  const { validate, errors } = validator('https://example.test/wp-content/plugins/x/assets/lib/pdfjs/web/viewer.html');
  validate('https://example.test/wp-content/uploads/report.pdf');
  assert.deepEqual(errors, []);
});

test('the viewer refuses a file from another origin and reports the error', () => {
  const { validate, errors } = validator('https://example.test/wp-content/plugins/x/assets/lib/pdfjs/web/viewer.html');
  assert.throws(() => validate('https://elsewhere.test/report.pdf'), /file origin does not match viewer's/);
  assert.deepEqual(errors, [['pdfjs-loading-error', "file origin does not match viewer's"]]);
});

test('the viewer refuses a scheme-relative file on another host', () => {
  const { validate } = validator('https://example.test/wp-content/plugins/x/assets/lib/pdfjs/web/viewer.html');
  assert.throws(() => validate('//elsewhere.test/report.pdf'), /file origin does not match viewer's/);
});
