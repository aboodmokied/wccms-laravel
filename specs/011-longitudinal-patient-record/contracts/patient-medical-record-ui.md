# Contract: Patient Longitudinal Medical Record UI

## HTTP Contract

### Show the longitudinal record

- **Method and path**: `GET /patients/{patient}`
- **Named route**: `patients.show`
- **Middleware**: Existing `hospital.jwt` web middleware.
- **Input**: Existing patient route key/code.
- **Success**: `200 text/html` containing the patient and all registration episodes.
- **Unknown patient**: `404` through route model binding.
- **Missing or invalid web session token**: Existing middleware redirect behavior.
- **Authenticated but disallowed account state/role**: Existing middleware denial behavior.
- **Mutation**: None.

### Existing linked resources

- Each registration links to `GET /registrations/{registration}` through `registrations.show`.
- Each visible wound photo uses the existing authenticated `wound_photos.show` route.
- This feature does not add a public API or fragment endpoint.

## Page Contract

### Patient level

1. Render the patient demographic banner once.
2. Render the total registration count and one longitudinal-history heading.
3. When no registration exists, render one clear Arabic empty state and no empty registration shell.

### Registration episode

Each registration is one `<details>` element with:

- A unique registration-based identifier and `data-registration-code` attribute.
- A machine-readable `data-registration-status` attribute.
- A `<summary>` that remains useful while collapsed.
- Status, registration date/time, department, responsible nurse, procedure when present, discharge date when applicable, registration code, and section counts.
- A direct `registrations.show` link reachable without opening the episode.

Ordering and disclosure:

1. Active registrations appear first.
2. Items within current and historical groups use registration date descending and registration code descending.
3. The first active registration is open initially.
4. If no active registration exists, the newest historical registration is open initially.
5. Other registrations remain collapsed but immediately discoverable.

### Expanded registration content

The expanded body is read-only and contains:

1. Registration information and relevant medical history.
2. Wounds/diagnoses with separate clinical employee and entry-user labels.
3. Each wound's baseline photos, rendered once.
4. Scheduled appointments with date, stored status, notes, creator, and fulfillment context when available.
5. A registration-scoped review timeline ordered newest first.

### Review contract

Each review is a uniquely identified nested disclosure containing:

- Visit type and date/time.
- Outcome and notes.
- Clinical employee and entry user as separate fields.
- Wound-specific observations, dressing, medication, and outcome fields.
- Only photos owned by that review detail.
- A baseline comparison reference or link when available, without rendering a second baseline copy.

The aggregate page must not expose an “add review” or other mutation call to action inside historical registration content. Focused actions remain available on the dedicated registration page according to existing rules.

## Ownership and Uniqueness Contract

- A registration element contains only items owned by that registration.
- A review contains only details owned by that review.
- A wound assessment refers only to a wound from the same registration.
- A clinical item or photo appears no more than once in the page.
- Repeated partials use registration-prefixed IDs; IDs such as `review-history` cannot be duplicated.

## Accessibility and RTL Contract

- Visible content and control labels are Arabic and use the existing RTL layout.
- Status and visit type are conveyed with text in addition to color.
- Native disclosure remains keyboard operable.
- Decorative icons use `aria-hidden="true"`.
- Focus remains visible with `:focus-visible` behavior.
- Dates and identifiers may use the existing LTR utility without changing the surrounding RTL reading order.
- Images use meaningful wound-specific Arabic alternative text and `loading="lazy"`.
- On narrow screens, registration summary metadata stacks while the dedicated-record link remains reachable.
- Existing dark-mode and reduced-motion conventions continue to apply.

## Performance Contract

- The controller must eager-load all rendered relationships; views must not execute database queries.
- Query growth must remain bounded as registrations and reviews grow.
- Image binary downloads defer through browser lazy loading.
- The page must satisfy the specification's under-two-second target for up to 10 registrations and 200 reviews in the target environment.
- If representative measurements fail, record timings and query plans before introducing pagination, fragment loading, caching, or new indexes.
