/**
 * The viewer opens only a PDF served from its own origin.
 *
 * `file` is resolved against the viewer's address first, so a scheme-relative address such as
 * `//other.example/a.pdf` is judged by the host it names, and `data:` and `javascript:` addresses
 * (whose origin is the string "null") never match.
 *
 * @param {string} file The `file` query value.
 * @param {string} viewerAddress The address of the page showing the viewer.
 * @throws {Error} When the PDF is on another origin.
 */
export function assertSameOrigin(file, viewerAddress) {
  const viewer = new URL(viewerAddress);

  if (new URL(file, viewer).origin !== viewer.origin) {
    throw new Error("file origin does not match viewer's");
  }
}
