<!-- cspell:ignore aligncenter alignleft alignright autoplay basebutton bgimg btncontent customclass Dismissable displayblock Fullsreen headingicon headingtype iconbox imgback imgcaption imgoverlay livewhale mediaposition mostlyleft mostlyright nomargin Pullquote Scribd soundcloud textalign textbgcolor textposition textwidth thinmargin threecolumn threequarters twocolumn twothirds uams wolfpackred youtubeui -->

Written against v0.7.0.

Every shortcode the plugin registers, with the attributes the Shortcake editor writes for it. Each one is added from **Add Post Element** in the classic editor (see [Adding your first shortcode](Adding-your-first-shortcode.md)), and can also be typed by hand with the same tag and attributes.

- **Tags** are the lowercased class name (`Image_Comparison` becomes `[image_comparison]`). A site can rename one with the `uams_shortcodes_shortcode_tag` filter ([Hooks](Hooks.md)).
- **Checkbox** attributes are written as `attr="true"` when checked and left out when not.
- **`customclass`** is a comma-separated list of extra CSS classes added to the outer element. Each one is passed through `sanitize_html_class()`.
- **Icon pickers** (`headingicon`) list every icon from the plugin's icon set; pick from the editor rather than typing a name.

## Layout and text

- [Alert](#alert), [Callout](#callout), [Icon Box](#icon-box), [Lead Paragraph](#lead-paragraph), [Pullquote](#pullquote), [Progress Bar](#progress-bar)
- [Row, 2 Columns](#row-2-columns), [Row, 3 Columns](#row-3-columns)
- [Basic Button](#basic-button), [UAMS Button](#uams-button)

## Media and embeds

- [PDF](#pdf), [Image Comparison](#image-comparison), [YouTube](#youtube), [Vimeo](#vimeo), [SoundCloud](#soundcloud), [Flickr](#flickr), [Twitter](#twitter)

## UAMS services

- [UAMS Calendar](#uams-calendar), [UAMS Calendar Widget](#uams-calendar-widget), [UAMS Map](#uams-map)

## Attributes

### Alert

`[alert]` wraps content: the **Alert Body** field is the text between `[alert]` and `[/alert]`.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `type` | Alert Type (radio) | `success` (Success), `info` (Information), `help` (Help), `warning` (Warning), `danger` (Danger) |
| `dismiss` | Dismissable? (checkbox) | `true` when checked |
| `customclass` | Custom CSS classes (text) | text |

### Basic Button

`[basebutton]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `btncontent` | Button Text (text) | text |
| `title` | Link Title (text) | text |
| `url` | Destination URL (url) | a URL |
| `target` | Target (radio) | `_top` (Same Tab/Window), `_blank` (New Tab/Window) |
| `color` | Style and Color (radio) | empty (Basic / Gray), `btn-default` (Default / Ghost), `btn-primary` (Primary / UAMS Red), `btn-success` (Success / Green), `btn-info` (Info / Blue), `btn-warning` (Warning / Yellow), `btn-danger` (Danger / Orange), `btn-link` (Link / None) |
| `size` | Size (radio) | empty (Default), `btn-lg` (Large), `btn-sm` (Small) |
| `displayblock` | Display full-width? (checkbox) | `true` when checked |
| `customclass` | Custom CSS classes (text) | text |

### UAMS Button

`[button]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `text` | Button Text (text) | text |
| `url` | Destination URL (url) | a URL |
| `target` | Target (radio) | `_top` (Same Tab/Window), `_blank` (New Tab/Window) |
| `color` | Color (radio) | empty (Default), `btn-red` (Red), `btn-gray` (Gray), `btn-green` (Green), `btn-blue` (Blue), `btn-yellow` (Yellow) |
| `size` | Size (radio) | empty (Default), `btn-lg` (Large), `btn-sm` (Small) |
| `type` | Icon (select) | `btn-go` (Default), `btn-plus` (Plus), `btn-external` (External), `btn-play` (Play), `btn-pdf` (PDF) |
| `newline` | New line? (checkbox) | `true` when checked |
| `customclass` | Custom CSS classes (text) | text |

### Callout

`[callout]` wraps content: the **Callout Body** field is the text between `[callout]` and `[/callout]`.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `heading` | Callout Heading (text) | text |
| `headingtype` | Heading Type (radio) | `p` (Paragraph), `h3` (Heading 3), `h2` (Heading 2), `h1` (Heading 1) |
| `headingicon` | Heading Icon (select) | one of 277 choices listed in the editor |
| `textalign` | Text Alignment (radio) | `text-left` (Left), `text-center` (Center), `text-right` (Right) |
| `url` | URL (url) | a URL |
| `target` | Open the link in a new window (checkbox) | `true` when checked |
| `type` | Callout Type (radio) | `basic` (Basic Callout), `img` (Callout with Image), `bgimg` (Callout with Background Image) |
| `bgcolor` | Callout Background Color (radio) | one of 15 choices listed in the editor |
| `img` | Image (attachment) | a media library attachment ID |
| `imgcaption` | Display Image Caption? (checkbox) | `true` when checked |
| `imgoverlay` | Image Adjustments (radio) | empty (None), `darken` (Darken (Dark overlay for light text)), `lighten` (Lighten (Light overlay for dark text)) |
| `textbgcolor` | Text Background Color (radio) | one of 20 choices listed in the editor |
| `mediaposition` | Media Position (radio) | `left` (Left), `right` (Right), `top` (Top), `bottom` (Bottom) |
| `imgback` | Image layout? (radio) | `front` (Full image), `back` (Cover background) |
| `textposition` | Text Position (radio) | `top left` (Top Left), `top center` (Top Center), `top right` (Top Right), `middle left` (Middle Left), `middle center` (Middle Center), `middle right` (Middle Right), `bottom left` (Bottom Left), `bottom center` (Bottom Center), `bottom right` (Bottom Right) |
| `textwidth` | Text Box Width (radio) | `col-lg-3 col-md-3 col-sm-12 col-xs-12` (One-Quarter Width), `col-lg-4 col-md-4 col-sm-12 col-xs-12` (One-Third Width), `col-lg-6 col-md-6 col-sm-12 col-xs-12` (One-Half Width), `col-lg-8 col-md-8 col-sm-12 col-xs-12` (Two-Thirds Width), `col-lg-9 col-md-9 col-sm-12 col-xs-12` (Three-Quarters Width), `col-lg-12 col-md-12 col-sm-12 col-xs-12` (Full-Width) |
| `margin` | Margins (radio) | `normal` (Normal), `thinmargin` (Thin), `nomargin` (None) |
| `padding` | Text Padding (radio) | `padding-one` (Normal (1 em)), `padding-two` (Large (2em)), `padding-halve` (Thin (0.5 em)), `padding-none` (None (0 em)) |
| `fullscreen` | Fullsreen? (checkbox) | `true` when checked |
| `customclass` | Custom CSS classes (text) | text |

### Flickr

`[flickr]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `url` | URL (text) | text |

### Icon Box

`[iconbox]` wraps content: the **Icon Box Body** field is the text between `[iconbox]` and `[/iconbox]`.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `type` | Icon Box Type (radio) | `default` (Default), `boxed` (Boxed), `framed` (Framed) |
| `bgcolor` | Background Color Option (radio) | `none` (Default), `red` (Red), `gray` (Gray), `blue` (Blue) |
| `heading` | Icon Box Heading (text) | text |
| `headingicon` | Heading Icon (select) | one of 277 choices listed in the editor |
| `url` | URL (url) | a URL |
| `customclass` | Custom CSS classes (text) | text |

### Image Comparison

`[image_comparison]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `left` | Left Image (attachment) | a media library attachment ID |
| `right` | Right Image (attachment) | a media library attachment ID |
| `position` | Slider Start Position (select) | `center` (Center), `mostlyleft` (Mostly Left), `mostlyright` (Mostly Right) |

### Lead Paragraph

`[lead]` wraps content: the **Lead Paragraph Text** field is the text between `[lead]` and `[/lead]`.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `customclass` | Custom CSS classes (text) | text |

### UAMS Calendar

`[livewhale]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `view` | Default View (select) | `all` (List), `day` (Day), `week` (Week), `month` (Month) |
| `group` | Group (select) | empty (All), `Admin` (Admin), `Faculty` (Faculty), `Main Calendar` (Main Calendar), `Public` (Public) |
| `type` | Event Type(s) (text) | text |
| `audience` | Audience (select) | empty (All), `Faculty` (Faculty), `Staff` (Staff), `Parents` (Parents), `Students` (Students) |
| `tags` | Tag(s) (text) | text |
| `exclude_tags` | Exclude Tag(s) (text) | text |
| `only_starred` | Only Starred (checkbox) | `true` when checked |
| `include_repeats` | Include Repeats (checkbox) | `true` when checked |
| `max` | Max number of items (number) | a number |
| `customclass` | Custom CSS classes (text) | text |

### UAMS Calendar Widget

`[livewhale_widget]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `id` | ID Number of Widget (number) | a number |
| `customclass` | Custom CSS classes (text) | text |

### UAMS Map

`[map]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `building` | Building (select) | one of 44 choices listed in the editor |
| `width` | Width (text) | text |
| `height` | Height (text) | text |
| `customclass` | Custom CSS classes (text) | text |

### PDF

`[pdf]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `attachment` | Select or upload PDF (attachment) | a media library attachment ID |
| `url` | ...or embed from URL (text) | text |

### Progress Bar

`[progressbar]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `complete` | Percentage Complete (number) | a number |
| `type` | Progress Bar Type (radio) | `progress-bar-primary` (Primary / UAMS Red), `progress-bar-success` (Success / Green), `progress-bar-info` (Info / Blue), `progress-bar-warning` (Warning / Yellow), `progress-bar-danger` (Danger / Orange) |
| `style` | Style (radio) | `default` (Default), `display-value` (Display Percentage Complete Value), `progress-striped` (Striped), `progress-striped active` (Striped and Animated) |
| `customclass` | Custom CSS classes (text) | text |

### Pullquote

`[pullquote]` wraps content: the **Quote Body** field is the text between `[pullquote]` and `[/pullquote]`.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `cite` | Citation (text) | text |
| `color` | Text Color (radio) | `wolfpackred` (Cardinal Red), `gray90` (90% Gray), `black` (Black) |
| `align` | Alignment (radio) | `alignleft` (Left), `aligncenter` (Center), `alignright` (Right) |
| `customclass` | Custom CSS classes (text) | text |

### SoundCloud

`[soundcloud]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `url` | URL (text) | text |
| `type` | Type (select) | `simple` (Simple), `visual` (Visual) |
| `autoplay` | Autoplay (select) | `0` (No), `1` (Yes) |

### Row, 3 Columns

`[threecolumn]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `col1` | Column 1 (textarea) | text |
| `col2` | Column 2 (textarea) | text |
| `col3` | Column 3 (textarea) | text |
| `ratio` | Column Width Ratio (radio) | `third-third-third` (33% / 33% / 33%), `quarter-quarter-half` (25% / 25% / 50%), `quarter-half-quarter` (25% / 50% / 25%), `half-quarter-quarter` (50% / 25% / 25%) |
| `customclass` | Custom CSS classes (text) | text |

### Twitter

`[twitter]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `url` | URL (text) | text |

### Row, 2 Columns

`[twocolumn]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `col1` | Column 1 (textarea) | text |
| `col2` | Column 2 (textarea) | text |
| `ratio` | Column Width Ratio (radio) | `half-half` (50% / 50%), `third-twothirds` (33% / 66%), `quarter-threequarters` (25% / 75%), `twothirds-third` (66% / 33%), `threequarters-quarter` (75% / 25%) |
| `customclass` | Custom CSS classes (text) | text |

### Vimeo

`[vimeo]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `url` | URL (text) | text |

### YouTube

`[youtubeui]` is self-closing.

| Attribute | Editor field | Values |
| --- | --- | --- |
| `url` | URL (text) | text |

## Not registered

The plugin also carries classes for `Facebook`, `GoogleDocs`, `Iframe`, `Instagram`, `Panel`, `RSS_Posts`, `Scribd`, `Script`, `TextBox` and the original `YouTube` shortcode. They are commented out of the registered list in `inc/class-uams-shortcodes.php`, so their tags do nothing on a site unless a site adds the class back through the `uams_shortcodes_shortcode_classes` filter ([Hooks](Hooks.md)).
