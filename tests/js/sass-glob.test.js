'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { root } = require('./helpers');

/**
 * What `sassGlob()` does to a Sass entry file, run over a scratch tree so the working tree is
 * never written to, and compiled the way `gulpfile.js` compiles it. The Sass entry files in the
 * repository use no glob import, so the Gulp build test cannot see a change in how the package
 * expands one.
 *
 * The assertions read the compiled CSS, not the emitted `@import` lines: `0.x` writes absolute
 * paths and `1.x` writes relative ones, and Sass resolves both to the same files.
 */
const gulp = require(path.join(root, 'node_modules', 'gulp'));
const sass = require(path.join(root, 'node_modules', 'gulp-sass'))(require(path.join(root, 'node_modules', 'sass')));
const sassGlob = require(path.join(root, 'node_modules', 'gulp-sass-glob'));
const { version } = require(path.join(root, 'node_modules', 'gulp-sass-glob', 'package.json'));

// `0.x` joins `file.base` into the glob pattern with the platform's separator, so on Windows its
// pattern never matches and it expands nothing. `1.x` normalizes the path and works everywhere.
const expandsNothing = process.platform === 'win32' && version.startsWith('0.');
const options = { skip: expandsNothing ? 'gulp-sass-glob 0.x expands no glob on Windows' : false };

const scratch = fs.mkdtempSync(path.join(os.tmpdir(), 'uamswp-shortcodes-sass-glob-'));
const write = (relativePath, contents) => {
  fs.mkdirSync(path.dirname(path.join(scratch, relativePath)), { recursive: true });
  fs.writeFileSync(path.join(scratch, relativePath), contents);
};

for (const name of ['b', 'a', 'c']) {
  write(`components/_${name}.scss`, `.${name} { order: 1; }\n`);
}
write('components/nested/_d.scss', '.d { order: 1; }\n');
write('_plain.scss', '.plain { order: 1; }\n');

test.after(() => fs.rmSync(scratch, { recursive: true, force: true }));

/** Compile one entry file through `sassGlob()` and `sass()`, and return the class names in the CSS, in order. */
function classes(source) {
  write('main.scss', source);
  return new Promise((resolve, reject) => {
    gulp.src('main.scss', { cwd: scratch })
      .pipe(sassGlob())
      .pipe(sass())
      .on('error', reject)
      .on('data', (file) => resolve([...file.contents.toString().matchAll(/^\.(\w+) \{/gm)].map((match) => match[1])));
  });
}

test('a glob import expands to every matching file, in alphabetical order', options, async () => {
  assert.deepEqual(await classes('@import "components/*";\n'), ['a', 'b', 'c']);
});

test('a recursive glob also matches files in nested folders', options, async () => {
  assert.deepEqual(await classes('@import "components/**/*";\n'), ['a', 'b', 'c', 'd']);
});

test('a glob that matches nothing imports nothing and the rest of the file still compiles', async () => {
  assert.deepEqual(await classes('@import "missing/*";\n.after { order: 1; }\n'), ['after']);
});

test('an import without a glob is left for Sass to resolve', async () => {
  assert.deepEqual(await classes('@import "plain";\n.rule { order: 1; }\n'), ['plain', 'rule']);
});
