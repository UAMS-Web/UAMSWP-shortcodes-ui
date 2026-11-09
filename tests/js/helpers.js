'use strict';

const fs = require('node:fs');
const path = require('node:path');
const vm = require('node:vm');

const root = path.resolve(__dirname, '..', '..');

/**
 * Run a plugin script in a fresh context with the given globals.
 *
 * The script is compiled with its real path as the file name so coverage is attributed to it.
 * Top-level `function` declarations become properties of the returned context.
 */
function loadScript(relativePath, globals = {}) {
  const file = path.join(root, relativePath);
  const context = vm.createContext({ ...globals });
  new vm.Script(fs.readFileSync(file, 'utf8'), { filename: file }).runInContext(context);
  return context;
}

/** A Shortcake attribute view: `model.get('attr')` names it and `$el` records show/hide. */
function attributeView(attr) {
  return {
    model: { get: (key) => (key === 'attr' ? attr : undefined) },
    $el: {
      visible: null,
      show() {
        this.visible = true;
      },
      hide() {
        this.visible = false;
      },
    },
  };
}

/** Build the collection of attribute views for the given names. */
function attributeCollection(...names) {
  return names.map(attributeView);
}

/** The visibility of each view, by attribute name: true, false or null (untouched). */
function visibility(collection) {
  return Object.fromEntries(collection.map((view) => [view.model.get('attr'), view.$el.visible]));
}

/** Globals for the admin scripts: a recording `wp.shortcake.hooks` and the one `_` function they use. */
function adminGlobals() {
  const actions = {};
  return {
    actions,
    globals: {
      wp: { shortcake: { hooks: { addAction: (name, callback) => (actions[name] = callback) } } },
      _: { find: (collection, predicate) => collection.find(predicate) },
    },
  };
}

module.exports = { root, loadScript, attributeCollection, visibility, adminGlobals };
