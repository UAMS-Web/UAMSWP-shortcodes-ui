'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { loadScript, attributeCollection, visibility, adminGlobals } = require('./helpers');

/** Load an admin script and return the listener registered under a hook name, with the hooks seen. */
function listener(script, hook) {
  const { actions, globals } = adminGlobals();
  loadScript(script, globals);
  return { callback: actions[hook], hooks: Object.keys(actions) };
}

test('enhanced-heading.coloroptions shows only the color the option names', () => {
  const { callback, hooks } = listener('admin/js/src/class-enhanced-heading.js', 'enhanced-heading.coloroptions');
  assert.deepEqual(hooks, ['enhanced-heading.coloroptions']);

  const cases = [
    ['text', { textcolor: true, bgcolor: false }],
    ['background', { textcolor: false, bgcolor: true }],
    ['none', { textcolor: false, bgcolor: false }],
  ];
  for (const [value, expected] of cases) {
    const views = attributeCollection('textcolor', 'bgcolor');
    callback({ value }, views, {});
    assert.deepEqual(visibility(views), expected, value);
  }
});

test('enhanced-heading.coloroptions leaves both fields alone when the value is undefined', () => {
  const { callback } = listener('admin/js/src/class-enhanced-heading.js', 'enhanced-heading.coloroptions');
  const views = attributeCollection('textcolor', 'bgcolor');
  callback({}, views, {});
  assert.deepEqual(visibility(views), { textcolor: null, bgcolor: null });
});

test('livewhale.view hides the count for the month view and shows it for any other', () => {
  const { callback } = listener('admin/js/src/class-livewhale.js', 'livewhale.view');

  for (const [value, expected] of [['month', false], ['list', true], ['', true]]) {
    const views = attributeCollection('max');
    callback({ value }, views, {});
    assert.equal(views[0].$el.visible, expected, JSON.stringify(value));
  }
});

test('recent-posts.limit_tax shows the taxonomy fields when the limit is on', () => {
  const { callback } = listener('admin/js/src/class-recent-posts.js', 'recent-posts.limit_tax');

  for (const [value, expected] of [['1', true], [true, true], ['', false], [0, false]]) {
    const views = attributeCollection('taxonomy', 'tax_term');
    callback({ value }, views, {});
    assert.deepEqual(visibility(views), { taxonomy: expected, tax_term: expected }, JSON.stringify(value));
  }
  const untouched = attributeCollection('taxonomy', 'tax_term');
  callback({}, untouched, {});
  assert.deepEqual(visibility(untouched), { taxonomy: null, tax_term: null });
});

test('callout.heading shows the heading fields when a heading is given', () => {
  const { callback } = listener('admin/js/src/class-callout.js', 'callout.heading');

  for (const [value, expected] of [['Title', true], ['', false]]) {
    const views = attributeCollection('headingtype', 'headingicon');
    callback({ value }, views, {});
    assert.deepEqual(visibility(views), { headingtype: expected, headingicon: expected }, JSON.stringify(value));
  }
  const untouched = attributeCollection('headingtype', 'headingicon');
  callback({}, untouched, {});
  assert.deepEqual(visibility(untouched), { headingtype: null, headingicon: null });
});

test('callout.url shows the target field when a URL is given', () => {
  const { callback } = listener('admin/js/src/class-callout.js', 'callout.url');

  for (const [value, expected] of [['https://example.test', true], ['', false]]) {
    const views = attributeCollection('target');
    callback({ value }, views, {});
    assert.equal(views[0].$el.visible, expected, JSON.stringify(value));
  }
  const untouched = attributeCollection('target');
  callback({}, untouched, {});
  assert.equal(untouched[0].$el.visible, null);
});

const typeFields = [
  'bgcolor', 'textbgcolor', 'img', 'imgcaption', 'imgback', 'vidtype', 'youtube', 'vimeo',
  'vidsource', 'autoplay', 'fallbackimg', 'mediaposition', 'textposition', 'imgoverlay', 'textwidth',
];

/** The fields the callout type listener touches, and what it does to each, per type. */
const typeExpectations = {
  basic: { bgcolor: true, textbgcolor: false, img: false, imgcaption: false, imgback: false, mediaposition: false, textposition: false, imgoverlay: false, textwidth: false },
  img: { bgcolor: true, textbgcolor: false, img: true, imgcaption: true, imgback: true, mediaposition: true, textposition: false, imgoverlay: false, textwidth: false },
  vid: { bgcolor: true, textbgcolor: false, img: false, imgcaption: false, imgback: false, mediaposition: true, textposition: false, imgoverlay: false, textwidth: false },
  bgimg: { textbgcolor: true, bgcolor: false, img: true, imgcaption: true, mediaposition: false, textposition: true, imgoverlay: true, textwidth: true },
  bgvid: { textbgcolor: true, bgcolor: false, img: false, imgcaption: false, imgback: false, mediaposition: false, textposition: true, imgoverlay: true, textwidth: true },
  unknown: { bgcolor: false, textbgcolor: false, img: false, imgcaption: false, imgback: false, mediaposition: false, textposition: false, imgoverlay: false, textwidth: false },
};

for (const [type, expected] of Object.entries(typeExpectations)) {
  test(`callout.type "${type}" sets the visibility of its fields`, () => {
    const { callback } = listener('admin/js/src/class-callout.js', 'callout.type');
    const views = attributeCollection(...typeFields);
    callback({ value: type }, views, {});

    const touched = Object.fromEntries(Object.entries(visibility(views)).filter(([, shown]) => shown !== null));
    assert.deepEqual(touched, expected);
  });
}

test('callout.type leaves every field alone when the value is undefined', () => {
  const { callback } = listener('admin/js/src/class-callout.js', 'callout.type');
  const views = attributeCollection(...typeFields);
  callback({}, views, {});
  assert.ok(Object.values(visibility(views)).every((shown) => shown === null));
});

const vidtypeExpectations = {
  youtube: { youtube: true, vimeo: false, vidsource: false, autoplay: false, fallbackimg: false },
  vimeo: { youtube: false, vimeo: true, vidsource: false, autoplay: false, fallbackimg: false },
  other: { youtube: false, vimeo: false, vidsource: true, autoplay: true, fallbackimg: true },
  unknown: { youtube: false, vimeo: false, vidsource: false, autoplay: false, fallbackimg: false },
};

for (const [vidtype, expected] of Object.entries(vidtypeExpectations)) {
  test(`callout.vidtype "${vidtype}" sets the visibility of its fields`, () => {
    const { callback } = listener('admin/js/src/class-callout.js', 'callout.vidtype');
    const views = attributeCollection('youtube', 'vimeo', 'vidsource', 'autoplay', 'fallbackimg');
    callback({ value: vidtype }, views, {});
    assert.deepEqual(visibility(views), expected);
  });
}

test('callout.vidtype leaves every field alone when the value is undefined', () => {
  const { callback } = listener('admin/js/src/class-callout.js', 'callout.vidtype');
  const views = attributeCollection('youtube', 'vimeo', 'vidsource', 'autoplay', 'fallbackimg');
  callback({}, views, {});
  assert.ok(Object.values(visibility(views)).every((shown) => shown === null));
});

test('the callout script registers exactly four hooks', () => {
  const { hooks } = listener('admin/js/src/class-callout.js', 'callout.type');
  assert.deepEqual(hooks, ['callout.heading', 'callout.url', 'callout.type', 'callout.vidtype']);
});

test('the empty and fully commented scripts register nothing', () => {
  for (const script of ['admin/js/src/class-button.js', 'admin/js/src/uams-syndication-shortcake.js']) {
    const { actions, globals } = adminGlobals();
    loadScript(script, globals);
    assert.deepEqual(Object.keys(actions), [], script);
  }
});
