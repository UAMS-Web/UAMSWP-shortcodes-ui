'use strict';

const test = require('node:test');
const assert = require('node:assert/strict');
const { loadScript } = require('./helpers');

/** A fake element: attributes, jQuery data, its parent's width, and a record of what was done to it. */
function element({ attrs = {}, data = {}, parentWidth = 640 } = {}) {
  return { attrs, data: { ...data }, parentWidth, css: {}, triggered: [] };
}

/**
 * Load shortcake-bakery.js against a minimal jQuery.
 *
 * `elements` are what `$('.shortcake-bakery-responsive')` finds. The fake runs `ready` callbacks at once,
 * records the `resize` handler and every timer, and answers `window.self !== window.top` from `inIframe`.
 */
function load({ elements = [], inIframe = false, parentInnerWidth = 300 } = {}) {
  const record = { resize: null, timers: [], cleared: [] };
  const windowObject = {};
  windowObject.self = windowObject;
  windowObject.top = inIframe ? {} : windowObject;
  const documentObject = {};

  const wrap = (el) => ({
    attr: (name) => el.attrs[name],
    data(key, value) {
      if (value === undefined) {
        return el.data[key];
      }
      el.data[key] = value;
      return this;
    },
    parent: () => ({ width: () => el.parentWidth }),
    css(property, value) {
      el.css[property] = value;
      return this;
    },
    trigger(name) {
      el.triggered.push(name);
    },
  });

  const jQuery = (subject) => {
    if (subject === documentObject) {
      return { ready: (callback) => callback() };
    }
    if (subject === windowObject) {
      return { on: (name, handler) => (record.resize = { name, handler }) };
    }
    if (typeof subject === 'string') {
      return {
        length: elements.length,
        each(callback) {
          elements.forEach((el) => callback.call(el));
        },
      };
    }
    return wrap(subject);
  };

  loadScript('assets/js/shortcake-bakery.js', {
    jQuery,
    document: documentObject,
    window: windowObject,
    parent: { innerWidth: parentInnerWidth },
    setTimeout: (callback, wait) => {
      record.timers.push({ callback, wait });
      return record.timers.length;
    },
    clearTimeout: (id) => record.cleared.push(id),
  });

  return record;
}

test('does nothing when the page has no responsive embeds', () => {
  const record = load({ elements: [] });
  assert.equal(record.resize, null);
});

test('keeps the declared proportion of a fixed-size embed and fits it to its parent', () => {
  const el = element({ attrs: { height: '315', width: '560' }, parentWidth: 280 });
  load({ elements: [el] });

  assert.equal(el.data['true-height'], '315');
  assert.equal(el.data['true-width'], '560');
  assert.equal(el.css.height, '157.5px');
  assert.equal(el.css.width, '280px');
  assert.deepEqual(el.triggered, ['shortcake-bakery-responsive-resize']);
});

test('uses a 640 by 360 proportion when the embed declares no size', () => {
  const el = element({ attrs: {}, parentWidth: 320 });
  load({ elements: [el] });

  assert.equal(el.data['true-height'], undefined);
  assert.equal(el.css.height, '180px');
  assert.equal(el.css.width, '320px');
});

test('does not store a percentage size as the true size', () => {
  const heightPercent = element({ attrs: { height: '50%', width: '560' } });
  const widthPercent = element({ attrs: { height: '315', width: '100%' } });
  load({ elements: [heightPercent, widthPercent] });

  assert.equal(heightPercent.data['true-height'], undefined);
  assert.equal(widthPercent.data['true-width'], undefined);
});

test('does not overwrite a true size the embed already carries', () => {
  const el = element({
    attrs: { height: '100', width: '200' },
    data: { 'true-height': 90, 'true-width': 160 },
    parentWidth: 320,
  });
  load({ elements: [el] });

  assert.equal(el.data['true-height'], 90);
  assert.equal(el.data['true-width'], 160);
  assert.equal(el.css.height, '180px');
});

test('does not store the size of an embed that has only one dimension', () => {
  const el = element({ attrs: { width: '560' } });
  load({ elements: [el] });

  assert.equal(el.data['true-width'], undefined);
});

test('measures the frame instead of the parent element when the page is inside an iframe', () => {
  const el = element({ attrs: {}, parentWidth: 999 });
  load({ elements: [el], inIframe: true, parentInnerWidth: 320 });

  assert.equal(el.css.width, '320px');
  assert.equal(el.css.height, '180px');
});

test('re-fits the embeds on resize, once, 100 milliseconds after the last resize', () => {
  const el = element({ attrs: {}, parentWidth: 640 });
  const record = load({ elements: [el] });

  assert.equal(record.resize.name, 'resize');
  el.parentWidth = 320;
  record.resize.handler();
  record.resize.handler();

  assert.equal(record.timers.length, 2);
  assert.ok(record.timers.every((timer) => timer.wait === 100));
  assert.equal(record.cleared.length, 2);
  assert.equal(el.css.width, '640px');

  record.timers[1].callback();
  assert.equal(el.css.width, '320px');
  assert.equal(el.css.height, '180px');
  assert.equal(el.triggered.length, 2);
});
