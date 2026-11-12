/**
 * First-party PDF viewer for the [pdf] shortcode, built on the pdf.js building blocks in
 * `assets/lib/pdfjs/` (copied from the `pdfjs-dist` package by `npx gulp`).
 *
 * Controls kept: previous and next page, a page number, zoom, find, download and print.
 * Controls dropped from the stock viewer: the sidebar and thumbnails, presentation mode, the
 * text-selection and hand tools, document properties, open file, and annotation editing.
 */
import * as pdfjs from '../lib/pdfjs/build/pdf.mjs';
import { EventBus, PDFFindController, PDFLinkService, PDFViewer } from '../lib/pdfjs/web/pdf_viewer.mjs';
import { assertSameOrigin } from './file-origin.mjs';

const lib = new URL('../lib/pdfjs/', import.meta.url).href;
const $ = (id) => document.getElementById(id);

const message = $('message');

function fail(text) {
  message.textContent = text;
  message.hidden = false;
}

/** Keep the viewer below the toolbar, which wraps onto more lines on a narrow screen. */
function followToolbarHeight() {
  const toolbar = document.querySelector('.toolbar');
  const apply = () => document.documentElement.style.setProperty('--toolbar-height', `${toolbar.offsetHeight}px`);

  apply();
  new ResizeObserver(apply).observe(toolbar);
}

async function main() {
  followToolbarHeight();

  const requested = new URLSearchParams(window.location.search).get('file');

  if (!requested) {
    fail('No PDF was given.');
    return;
  }

  let file;

  try {
    file = assertSameOrigin(requested, window.location.href);
  } catch {
    fail('This PDF is on another site, so it cannot be shown here.');
    return;
  }

  pdfjs.GlobalWorkerOptions.workerSrc = `${lib}build/pdf.worker.mjs`;

  const eventBus = new EventBus();
  const linkService = new PDFLinkService({ eventBus });
  const findController = new PDFFindController({ eventBus, linkService });
  const viewer = new PDFViewer({
    container: $('viewerContainer'),
    viewer: $('viewer'),
    eventBus,
    linkService,
    findController,
    annotationMode: pdfjs.AnnotationMode.ENABLE,
  });
  linkService.setViewer(viewer);

  const pageNumber = $('pageNumber');
  const zoom = $('zoom');
  const findInput = $('findInput');
  const findStatus = $('findStatus');

  $('download').href = file;
  $('print').addEventListener('click', () => printFile(file));

  $('previous').addEventListener('click', () => viewer.previousPage());
  $('next').addEventListener('click', () => viewer.nextPage());
  pageNumber.addEventListener('change', () => {
    const page = Number.parseInt(pageNumber.value, 10);

    if (page >= 1 && page <= viewer.pagesCount) {
      viewer.currentPageNumber = page;
    } else {
      pageNumber.value = viewer.currentPageNumber;
    }
  });

  $('zoomIn').addEventListener('click', () => viewer.increaseScale());
  $('zoomOut').addEventListener('click', () => viewer.decreaseScale());
  zoom.addEventListener('change', () => {
    viewer.currentScaleValue = zoom.value;
  });
  window.addEventListener('resize', () => {
    const value = viewer.currentScaleValue;

    if (['auto', 'page-fit', 'page-width'].includes(value)) {
      viewer.currentScaleValue = value;
    }
  });

  const find = (type, findPrevious) => {
    eventBus.dispatch('find', {
      source: findInput,
      type,
      query: findInput.value,
      caseSensitive: false,
      entireWord: false,
      highlightAll: true,
      findPrevious,
      matchDiacritics: false,
    });
  };
  $('find').addEventListener('submit', (event) => {
    event.preventDefault();
    find('again', event.submitter === $('findPrevious'));
  });
  findInput.addEventListener('input', () => find('', false));
  $('findNext').addEventListener('click', () => find('again', false));
  $('findPrevious').addEventListener('click', () => find('again', true));

  eventBus.on('pagesinit', () => {
    $('pageCount').textContent = viewer.pagesCount;
    pageNumber.max = viewer.pagesCount;
    viewer.currentScaleValue = zoom.value;
  });
  eventBus.on('pagechanging', ({ pageNumber: current }) => {
    pageNumber.value = current;
    $('previous').disabled = current <= 1;
    $('next').disabled = current >= viewer.pagesCount;
  });
  eventBus.on('scalechanging', ({ scale, presetValue }) => {
    const value = presetValue || String(scale);
    let option = [...zoom.options].find((candidate) => candidate.value === value);

    if (!option) {
      option = zoom.querySelector('option[data-custom]') ?? zoom.appendChild(Object.assign(document.createElement('option'), { hidden: true }));
      option.dataset.custom = '';
      option.value = value;
      option.textContent = `${Math.round(scale * 100)}%`;
    }
    zoom.value = value;
  });
  eventBus.on('updatefindmatchescount', ({ matchesCount: { current, total } }) => {
    findStatus.textContent = total ? `${current} of ${total}` : '';
  });
  eventBus.on('updatefindcontrolstate', ({ state, matchesCount: { current, total } }) => {
    // 1 is FindState.NOT_FOUND; a found or pending search reports through the match count.
    findStatus.textContent = state === 1 ? 'Not found' : total ? `${current} of ${total}` : '';
  });

  try {
    const pdfDocument = await pdfjs.getDocument({
      url: file,
      cMapUrl: `${lib}cmaps/`,
      cMapPacked: true,
      iccUrl: `${lib}iccs/`,
      standardFontDataUrl: `${lib}standard_fonts/`,
      wasmUrl: `${lib}wasm/`,
      enableXfa: false,
    }).promise;

    viewer.setDocument(pdfDocument);
    linkService.setDocument(pdfDocument, null);
  } catch {
    fail('The PDF could not be loaded.');
  }
}

/**
 * Print the PDF through a hidden frame, which is the browser's own PDF printing and works because
 * the file is on this page's origin (the check above refuses any other).
 */
function printFile(file) {
  const frame = Object.assign(document.createElement('iframe'), { src: file });

  frame.style.cssText = 'position:fixed;width:0;height:0;border:0;visibility:hidden';
  frame.addEventListener('load', () => {
    frame.contentWindow.focus();
    frame.contentWindow.print();
  });
  document.body.appendChild(frame);
}

main();
