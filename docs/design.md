# SIMSIDANG — Frontend Design System

**Stack:** Livewire 4 + Flux 2 + Tailwind CSS 4 + Blade (no React).
**Approach:** shadcn/ui-inspired design language (tokens, radius, spacing, component anatomy) implemented with **Flux 2 components** + Tailwind utilities. shadcn itself is React-only and is NOT installed; we borrow its *aesthetic*, not its code.

---

> **Theme accent: Teal/Cyan.** Flux `accent` = `teal-600` (light) / `teal-400` (dark), set in `resources/css/app.css` `@theme`. Hardcoded primary buttons use `bg-teal-600 hover:bg-teal-700`; links use `text-teal-600 dark:text-teal-400`. Sidebar/header surfaces tinted `bg-teal-50/40` + `border-teal-100`.

## 1. Design Principles
- **Neutral & clean**: zinc/neutral palette, generous whitespace, subtle borders over heavy shadows.
- **Dark mode first-class**: every surface must define light + `dark:` variant (existing views already do).
- **Accessible**: focus rings visible, min contrast AA, semantic headings, labels tied to inputs.
- **Consistency**: reuse Flux components before writing custom Blade/Tailwind.

---

## 2. Design Tokens (shadcn-inspired)

Map into Tailwind 4 `@theme` in `resources/css/app.css`. Values are HSL, aligned to shadcn "zinc" theme.

| Token | Light | Dark |
|---|---|---|
| `--color-background` | `0 0% 100%` | `240 10% 3.9%` |
| `--color-foreground` | `240 10% 3.9%` | `0 0% 98%` |
| `--color-muted` | `240 4.8% 95.9%` | `240 3.7% 15.9%` |
| `--color-muted-foreground` | `240 3.8% 46.1%` | `240 5% 64.9%` |
| `--color-border` | `240 5.9% 90%` | `240 3.7% 15.9%` |
| `--color-primary` (accent) | `teal-600` | `teal-400` |
| `--color-primary-foreground` | `white` | `teal-950` |
| `--color-destructive` | `0 84.2% 60.2%` | `0 62.8% 30.6%` |
| `--color-ring` | `240 5.9% 10%` | `240 4.9% 83.9%` |

**Radius:** base `--radius: 0.5rem` → `rounded-lg` primary, `rounded-xl` for cards/panels (matches current views).
**Spacing:** 4px scale (Tailwind default). Card padding `p-4`, section gap `gap-4`.
**Typography:** Instrument Sans (already loaded via `vite.config.js` bunny fonts), weights 400/500/600. Headings `font-semibold`/`font-bold`.

> Current views standardize on: cards `rounded-xl border border-neutral-200 dark:border-neutral-700 p-4`, primary button `bg-teal-600 hover:bg-teal-700 text-white` (teal accent per theme above).

---

## 3. Component Inventory (UI need → Flux 2)

| UI need | Flux 2 component |
|---|---|
| Text input / textarea | `flux:input`, `flux:textarea` |
| Field + label + error | `flux:field`, `flux:label`, `flux:error` |
| Select (single) | `flux:select` |
| **Multi-select (assign dosen)** | `flux:select variant="listbox" multiple` |
| Button | `flux:button` (`variant="primary\|ghost\|danger"`) |
| Table | `flux:table`, `flux:table.row`, `flux:table.cell` |
| Status pill | `flux:badge` (color by status) |
| Dropdown / user menu | `flux:dropdown`, `flux:menu` (already used) |
| Modal (delete confirm) | `flux:modal` |
| Flash / callout | `flux:callout` |
| Avatar | `flux:avatar` (already used) |

---

## 4. Status Badge Mapping

Submission `status`:
| Value | Badge color | Label |
|---|---|---|
| `pending` | `zinc` | Menunggu |
| `sidang_berjalan` | `blue` | Sidang Berjalan |
| `revisi` | `amber` | Revisi |
| `selesai` | `green` | Selesai |

Revision `status_poin`:
| Value | Badge color | Label |
|---|---|---|
| `open` | `amber` | Belum Disetujui |
| `resolved` | `green` | Disetujui |

---

## 5. Screen Blueprints

### Admin — Schedule Create/Edit (with dosen & mahasiswa plotting, MVP-01)
```blade
{{-- Dosen Penguji: Alpine checkbox list with select-all/clear-all --}}
<flux:field>
    <flux:label>Dosen Penguji</flux:label>
    <div x-data="{ selectedIds: {{ json_encode(old('dosen_ids', $schedule->dosens->pluck('id')->all() ?? [])) }} }">
        <flux:button size="xs" x-on:click="selectedIds = {{ json_encode($dosens->pluck('id')) }}">Pilih Semua</flux:button>
        <flux:button size="xs" x-on:click="selectedIds = []">Hapus Semua</flux:button>
        <div class="mt-2 max-h-48 overflow-y-auto space-y-1">
            @foreach ($dosens as $dosen)
                <label class="flex items-center gap-2 text-sm">
                    <input type="checkbox" name="dosen_ids[]" value="{{ $dosen->id }}"
                        x-model="selectedIds" :checked="selectedIds.includes({{ $dosen->id }})">
                    {{ $dosen->name }}
                </label>
            @endforeach
        </div>
    </div>
    <flux:error name="dosen_ids" />
</flux:field>
```

Picker UX (dosen/mahasiswa) same pattern: Alpine checkbox list with search input, counter badge, select-all/clear-all. Mahasiswa search filters by name or NIM.

### Admin — Users / Schedules Index
- `flux:table` for rows; actions column uses `flux:button variant="ghost"` (edit) + `flux:modal` trigger (delete confirm).

### Dosen Dashboard
- One card per assigned schedule (today), `flux:table` of mahasiswa submissions, status via `flux:badge`.

### Mahasiswa Dashboard
- Submission summary card + revision notes list; each note `flux:badge` status + "Tanggapi" action.

---

## 6. Conventions
- Layout wrapper: `<x-layouts::app :title="...">` (existing).
- Indonesian UI copy.
- Buttons: primary action right-aligned in forms; destructive = `variant="danger"` + confirm modal.
- Never inline hex colors; use tokens / Tailwind semantic classes.
- Run `npm run build` (or `npm run dev`) after view/CSS changes.
