'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { spawnSync } = require('node:child_process');
const { root } = require('./helpers');

/**
 * Run the Gulp build in a scratch copy of the sources and compare what it writes with the
 * files committed in the repository. The working tree is never written to.
 */
const scratch = fs.mkdtempSync(path.join(os.tmpdir(), 'uamswp-shortcodes-build-'));

for (const entry of ['assets/css', 'admin/css', 'admin/js/src']) {
  fs.cpSync(path.join(root, entry), path.join(scratch, entry), { recursive: true });
}
fs.copyFileSync(path.join(root, 'gulpfile.js'), path.join(scratch, 'gulpfile.js'));
fs.symlinkSync(path.join(root, 'node_modules'), path.join(scratch, 'node_modules'), 'junction');

const gulp = path.join(root, 'node_modules', 'gulp', 'bin', 'gulp.js');
const build = spawnSync(process.execPath, [gulp], { cwd: scratch, encoding: 'utf8' });
const tasks = spawnSync(process.execPath, [gulp, '--tasks-simple'], { cwd: scratch, encoding: 'utf8' });

test.after(() => fs.rmSync(scratch, { recursive: true, force: true }));

/** The index of the first byte at which two buffers differ, or -1 when they are equal. */
function firstDifference(a, b) {
  const length = Math.min(a.length, b.length);
  for (let i = 0; i < length; i++) {
    if (a[i] !== b[i]) {
      return i;
    }
  }
  return a.length === b.length ? -1 : length;
}

function assertSameBytes(relativePath) {
  const built = fs.readFileSync(path.join(scratch, relativePath));
  const committed = fs.readFileSync(path.join(root, relativePath));
  assert.ok(built.length > 0, `${relativePath} was built empty`);
  assert.equal(firstDifference(built, committed), -1, `${relativePath} differs from the committed file at byte ${firstDifference(built, committed)}`);
}

test('the default Gulp task completes', () => {
  assert.equal(build.status, 0, build.stderr);
  assert.match(build.stdout, /Finished 'default'/);
});

test('the default task is the only registered task and runs the three build tasks', () => {
  assert.equal(tasks.stdout.trim(), 'default');
  for (const name of ['style', 'admin', 'adminScripts']) {
    assert.match(build.stdout, new RegExp(`Finished '${name}'`), name);
  }
});

test('style builds assets/css/main.css exactly as committed', () => {
  assertSameBytes('assets/css/main.css');
});

test('admin builds admin/css/admin.css exactly as committed', () => {
  assertSameBytes('admin/css/admin.css');
});

test('adminScripts concatenates admin/js/src into admin/js/build/main.js as committed, ignoring line endings', () => {
  const normalize = (file) => fs.readFileSync(file, 'utf8').replace(/\r\n/g, '\n');
  const built = normalize(path.join(scratch, 'admin/js/build/main.js'));
  const committed = normalize(path.join(root, 'admin/js/build/main.js'));

  assert.ok(built.length > 0);
  assert.equal(built, committed);
});

test('there is no front-end script task, source or build file', () => {
  assert.doesNotMatch(build.stdout, /frontEndScripts/);
  assert.equal(fs.existsSync(path.join(root, 'assets/js/src')), false);
  assert.equal(fs.existsSync(path.join(root, 'assets/js/build/main.js')), false);
  assert.equal(fs.existsSync(path.join(scratch, 'assets/js/build/main.js')), false);
});

test('the first-difference helper reports where two buffers part', () => {
  assert.equal(firstDifference(Buffer.from('abc'), Buffer.from('abc')), -1);
  assert.equal(firstDifference(Buffer.from('abc'), Buffer.from('abd')), 2);
  assert.equal(firstDifference(Buffer.from('ab'), Buffer.from('abc')), 2);
});
