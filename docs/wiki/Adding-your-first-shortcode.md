<!-- cspell:ignore UAMS UAMSWP -->

Written against v0.7.0.

At the end of this walkthrough a page shows a callout box, inserted through the editor's form rather than typed by hand.

**Note:** Shortcake works in the classic editor. In the block editor there is no form: a shortcode is typed into a Shortcode block by hand, with the tags and attributes in [Shortcodes](Shortcodes.md).

1. **Check both plugins are active.** In **Plugins**, both *Shortcake (Shortcode UI)* and *UAMSWP Shortcodes UI* show as active. With Shortcake missing, the dashboard shows a notice instead ([Troubleshooting](Troubleshooting.md#the-dashboard-says-shortcake-is-required)).
2. **Open a page in the editor** and put the cursor where the callout should go.
3. **Choose Add Post Element** above the editor (or **Add Media**, then **Insert Post Element** in the left column). A grid of elements appears: Alert, Callout, UAMS Button, Row, 2 Columns and the rest.
4. **Choose Callout.** A form opens with the callout's fields.
5. **Fill in the form:** a **Callout Heading**, some text in **Callout Body**, *Basic Callout* as the **Callout Type** and a **Callout Background Color**.
6. **Choose Insert Element.** The editor shows a preview of the callout in place. In the Text tab the same element reads as a shortcode, for example `[callout heading="Visiting hours" type="basic" bgcolor="lightblue"]Open daily.[/callout]`.
7. **Preview or update the page.** The callout renders on the site with the heading, the body and the chosen color.

To change it later, select the preview in the Visual tab and choose the pencil icon: the same form opens with the saved values.

## Next

- [Shortcodes](Shortcodes.md) lists every element and attribute the form can set.
- [Embedding a PDF](Embedding-a-PDF.md) walks through an element with more moving parts.
