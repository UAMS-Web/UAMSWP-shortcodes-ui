'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..', '..');
const viewer = fs.readFileSync(path.join(root, 'assets/lib/pdfjs/web/viewer.js'), 'utf8');
const library = fs.readFileSync(path.join(root, 'assets/lib/pdfjs/build/pdf.js'), 'utf8');

/**
 * Load the vendored pdf.js library in a context that records every `Function()` construction,
 * optionally apply the viewer's `isEvalSupported` setting, and compile one glyph path.
 */
function compileGlyph(applyViewerSetting) {
  const compiled = [];
  const sandbox = {
    console,
    navigator: { userAgent: 'node' },
    document: {
      createElement: () => ({ getContext: () => ({}), style: {}, setAttribute() {} }),
      documentElement: { style: {} },
      head: {},
      body: {},
    },
    Function: function (...args) {
      compiled.push(args);
      return Function(...args);
    },
  };
  sandbox.Function.prototype = Function.prototype;
  sandbox.window = sandbox;
  sandbox.self = sandbox;
  const context = vm.createContext(sandbox);
  const expose = 'exports.PDFJS = pdfjsLibs.pdfjsDisplayGlobal.PDFJS;';
  assert.ok(library.includes(expose), 'the library export line the test hooks into is present');
  new vm.Script(
    library.replace(expose, `${expose} exports.__FontFaceObject = pdfjsLibs.pdfjsDisplayFontLoader.FontFaceObject;`),
    { filename: 'pdf.js' },
  ).runInContext(context);

  const lib = context.pdfjsDistBuildPdf;
  if (applyViewerSetting) {
    // The viewer sets this flag; the match below is the line in viewer.js that does so.
    const line = viewer.match(/^\s*PDFJS\.isEvalSupported = (true|false);$/m);
    assert.ok(line, 'viewer.js sets PDFJS.isEvalSupported');
    lib.PDFJS.isEvalSupported = line[1] === 'true';
  }
  const font = new lib.__FontFaceObject(
    { data: [], loadedName: 'f1', mimetype: 'x' },
    { isEvalSupported: lib.PDFJS.isEvalSupported, disableFontFace: false },
  );
  const objs = { get: () => [{ cmd: 'save' }, { cmd: 'transform', args: [1, 0, 0, 1, 0, 0] }, { cmd: 'restore' }] };
  compiled.length = 0;
  font.getPathGenerator(objs, 'a');
  return compiled.length;
}

test('the library compiles glyph paths with Function() when nothing turns it off', () => {
  assert.ok(compileGlyph(false) > 0);
});

test('the viewer setting stops glyph paths from being compiled with Function()', () => {
  assert.equal(compileGlyph(true), 0);
});

test('viewer.js sets isEvalSupported to false', () => {
  assert.match(viewer, /^\s*PDFJS\.isEvalSupported = false;$/m);
});
