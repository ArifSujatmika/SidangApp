# SIMSIDANG — Frontend Design System

**Stack:** Livewire 4 + Flux 2 + Tailwind CSS 4 + Blade (no React).
**Approach:** shadcn/ui-inspired design language (tokens, radius, spacing, component anatomy) implemented with **Flux 2 components** + Tailwind utilities. shadcn itself is React-only and is NOT installed; we borrow its *aesthetic*, not its code.

---

> **Theme accent: Yellow.** Flux `accent` = `yellow-500` (light) / `yellow-400` (dark), set in `resources/css/app.css` `@theme`. Hardcoded primary buttons use `bg-yellow-500 hover:bg-yellow-600`; links use `text-yellow-600 dark:text-yellow-400`. Sidebar/header surfaces tinted `bg-yellow-50/40` + `border-yellow-100`.

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
| `--color-primary` (accent) | `yellow-500` | `yellow-400` |
| `--color-primary-foreground` | `white` | `yellow-950` |
| `--color-destructive` | `0 84.2% 60.2%` | `0 62.8% 30.6%` |
| `--color-ring` | `240 5.9% 10%` | `240 4.9% 83.9%` |

**Yellow palette our light theme uses:**
- `yellow-500` (#eab308) - Primary accent, buttons, focus rings
- `yellow-600` (#ca8a04) - Hover state for primary buttons
- `yellow-700` (#a16207) - Badge backgrounds
- `yellow-400` (#facc15) - Dark mode primary accent
- `yellow-50` (#fefce8) - Background tints

**Radius:** base `--radius: 0.5rem` → `rounded-lg` primary, `rounded-xl` for cards/panels (matches current views).
**Spacing:** 4px scale (Tailwind default). Card padding `p-4`, section gap `gap-4`.
**Typography:** Instrument Sans (already loaded via `vite.config.js` bunny fonts), weights 400/500/600. Headings `font-semibold`/`font-bold`.

> Current views standardize on: cards `rounded-xl border border-neutral-200 dark:border-neutral-700 p-4`, primary button `bg-yellow-500 hover:bg-yellow-600 text-white` (yellow accent per theme above).

---

## 3. Component Inventory (UI need → Flux 2)

| UI need | Flux 2 component |
|---|---|
| Text input / textarea | `flux:input`, `flux:textarea` |
| Field + label + error | `flux:field`, `flux:label`, `flux:error` |
| Select (single) | `flux:select` |
| Multi-select (assign dosen) | Custom Alpine checkbox list with search (Flux wrapper `flux:field`) |
| Button | `flux:button` (`variant="primary|ghost|danger"`) |
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
@php($dosens = \App\Models\User::dosen()->get())
@php($mahasiswas = \App\Models\User::mahasiswa()->get())

<x-layouts::app :title="__('Tambah Jadwal')">
    <div class="flex h-full w-full flex-1 flex-col gap-4 rounded-xl">
        <h1 class="text-2xl font-bold">Tambah Jadwal</h1>

        <form method="POST" action="{{ route('admin.schedules.store') }}" class="grid gap-4 rounded-xl border border-neutral-200 p-4 dark:border-neutral-700">
            @csrf

            <flux:field>
                <flux:label>Nama Grup Sidang</flux:label>
                <input name="nama_grup_sidang" class="w-full rounded border px-3 py-2" required />
                <flux:error name="nama_grup_sidang" />
            </flux:field>

            <flux:field>
                <flux:label>Dosen Penguji</flux:label>
                <div
                    x-data="{
                        selected: @js(array_map('strval', old('dosen_ids', []))),
                        options: @js($dosens->pluck('id')->map(fn ($id) => (string) $id)->values()),
                        toggleAll() { this.selected = this.selected.length === this.options.length ? [] : [...this.options] },
                    }"
                >
                    <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
                        <flux:label>Dosen Penguji</flux:label>
                        <div class="flex items-center gap-3 text-xs">
                            <span class="rounded-full bg-yellow-50 px-2.5 py-1 font-medium text-yellow-700 dark:bg-yellow-950/50 dark:text-yellow-300" x-text="`${selected.length} dipilih`"></span>
                            <button type="button" class="font-medium text-yellow-600 hover:underline dark:text-yellow-400" @click="toggleAll()" x-text="selected.length === options.length && options.length ? 'Hapus semua' : 'Pilih semua'"></button>
                        </div>
                    </div>
                    <div class="max-h-48 space-y-1 overflow-y-auto rounded-lg border border-neutral-200 bg-white p-2 dark:border-neutral-700 dark:bg-neutral-900">
                        @forelse ($dosens as $dosen)
                            <label class="flex cursor-pointer items-center gap-3 rounded-md px-2 py-2 transition hover:bg-yellow-50 dark:hover:bg-neutral-800">
                                <input x-model="selected" type="checkbox" name="dosen_ids[]" value="{{ $dosen->id }}" class="size-4 rounded border-neutral-300 text-yellow-600 focus:ring-yellow-500">
                                <span class="text-sm font-medium">{{ $dosen->name }}</span>
                            </label>
                        @empty
                            <p class="px-2 py-3 text-center text-sm text-neutral-400">Belum ada data dosen.</p>
                        @endforelse
                    </div>
                </div>
                <flux:error name="dosen_ids" />
            </flux:field>

            <flux:button type="submit" variant="primary">Simpan</flux:button>
        </form>
    </div>
</x-layouts::app>
```

Picker UX (dosen/mahasiswa) is a custom Alpine checkbox list with search input, counter badge, select-all/clear-all. Mahasiswa search filters by name or NIM.

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

---

## 7. Theme Migration Notes

### Teal → Yellow (July 2026)
- Changed Flux accent from Teal to Yellow across the entire application
- Update `resources/css/app.css` `@theme` tokens
- Updated all teal color classes to yellow equivalents:
  - `bg-teal-600` → `bg-yellow-500`
  - `hover:bg-teal-700` → `hover:bg-yellow-600`
  - `bg-teal-50` → `bg-yellow-50`
  - `border-teal-100` → `border-yellow-100`
  - `text-teal-600` through `text-teal-950` → `text-yellow-600` through `text-yellow-900`
- Fixed yellow-specific classes: `yellow-400`, `yellow-50`, `yellow-700`, `positive-yellow-50`, etc.

- Added yellow color palette tokens to `resources/css/app.css`:
  - `--color-yellow-50` to `--color-yellow-900`
  - `--color-accent` = yellow-500 (light) / yellow-400 (dark)

### Standardization with Flux Components
- Converted raw HTML inputs/pickers to use `flux:field` with proper label/error placement
- Updated admin forms to use consistent form card wrapper pattern
- Consistent button size (`xs` small, default regular)
- Consistent table structure using `flux:table` components

(End of file)