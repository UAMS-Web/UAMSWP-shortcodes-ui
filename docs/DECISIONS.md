# Decision log

The canonical record of why the project is the way it is: the reasoning and the rejected alternatives behind decisions that have closed. It guards against silently reopening a settled decision.

**Consult it before acting.** Before proposing a change, reopening a question or picking an approach, check here first. The path under consideration may already be a decided-and-recorded one, or an explicitly rejected one. If it is, follow the record, or make the case to revisit it.

**When a decision closes, record the verdict in both places.** Capture the what, the why and the rejected alternatives here (the cross-ticket index) and in the GitHub issue's `## Decision` section (the per-ticket record), keeping the two consistent. Put the full reasoning in the relevant `docs/` companion when the decision touches a documented area. The issue tracker stays the system of record for a single ticket's status.

## How to add an entry

Entries run oldest first, one per closed decision, so a new entry is appended to the end of the file. Keep it terse; the companion or the issue carries the depth.

Every entry heading is `## YYYY-MM-DD`, a space, and a short title. Leave exactly one blank line above each heading. The check (`decision-log` skill) fails on a heading dated earlier than the one before it, and on a heading that sits directly under the previous line.

```markdown
## YYYY-MM-DD <short decision title> (<#issue or area>)

**Decision.** <what was decided, in a sentence or two>

**Why.** <the reasoning that settled it>

**Rejected.** <the alternatives considered, and why each lost>

**Companion.** <link to the docs/ companion or the GitHub issue holding the full reasoning>
```

---

## 2026-11-14 No plugin change for `use_modular_templates` (#23)

**Decision.** `master` does not send `use_modular_templates` in the `lwCalendar` call, and the plugin gains no switch for it. #23 is closed as not planned.

**Why.** Reading the production calendar theme (#126) found the option set to `true` in the host's own `calendar_settings.html`, so the host side is already handled. The embed's loader never reads options, and the theme script it loads is absent on both hosts. No content on the two networks that run the deployed copies uses the embed (#142). Neither site's WP Pusher is set to deploy on push, so the older `deployed/*` code on sites.uams.edu is not replaced by `master` unless someone updates it by hand (#143).

**Rejected.** A default-off switch in the plugin that each site enables in its own code: adds a setting and a code path that no live page exercises. Sending the option unconditionally: changes the embed for every caller to match two deployed copies, with nothing showing the embed reads it.

**Companion.** #23, #126, #142 and #143 on GitHub hold the full reasoning.
