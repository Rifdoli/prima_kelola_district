# Issue: Halaman Self Assessment Prima District + Gating Menu Administration

> **Untuk implementor (junior programmer / model AI):** Ikuti tahapan di bawah **berurutan**. Setiap tahap punya tujuan, contoh kode, dan checklist. Jangan mengubah file di luar yang disebutkan di "Ringkasan File yang Disentuh". Setelah selesai, jalankan seluruh langkah di bagian **Verifikasi**.
>
> Dokumen ini terdiri dari **2 bagian**:
> - **BAGIAN A** — Gating menu Administration (hanya super-admin yang bisa melihatnya).
> - **BAGIAN B** — Fitur Self Assessment (inti).
>
> **Baca dulu** seluruh "Konteks", "Sumber Data Master", dan "Keputusan Desain" sebelum coding. Beberapa keputusan **berbeda dari pola yang sudah ada** di codebase (khususnya middleware `admin`) — kalau dilewati, fiturnya selalu kena error 403.

---

## Konteks Singkat

Stack:
- **Backend**: Laravel (folder [backend/](backend/)). Primary key custom (`user_id`, `role_id`, `organization_id`), **bukan** `id`. Mass-assignment pakai PHP 8 attribute `#[Fillable([...])]` (bukan properti `$fillable`). Audit field `created_by`/`updated_by`/`uuid` diisi otomatis via `booted()` di model. Response API pakai trait `ApiResponse` (`$this->success($data, $message, $status)` / `$this->error($message, $status)`) — bukan Laravel Resource. Contoh pola: [backend/app/Http/Controllers/Api/UserController.php](backend/app/Http/Controllers/Api/UserController.php).
- **Frontend**: Vue 2 (LightAble template, folder [frontend/](frontend/)), Options API + Bootstrap-Vue. Axios instance di [frontend/src/services/api.js](frontend/src/services/api.js) (otomatis sisip Bearer token). Auth helper di [frontend/src/authutils.js](frontend/src/authutils.js); user login disimpan di `sessionStorage` key `authUser` (JSON) dan token di `authToken`.

Data & relasi yang sudah ada (jangan dibuat ulang):
- `organizations` → `organization_types` (kolom `level`: National=1, Area=2, Regional=3, District=4).
- `users` → `roles` (`role_id`) & `organizations` (`organization_id`).
- **Closure table** `organization_mapping` (`ancestor_id`, `descendant_id`, `depth`) sudah ada & terisi otomatis, **termasuk baris self-reference** (`ancestor_id == descendant_id`, `depth = 0`). Jadi `where('ancestor_id', $orgId)` otomatis mencakup organisasi itu sendiri + seluruh descendant. Logikanya di `app/Services/OrganizationMappingService.php` — **jangan diubah**.

Pemetaan role (sumber: `RoleSeeder.php`):

| sname role  | Nama role      | level org |
|-------------|----------------|-----------|
| `admin_sup` | SUPER ADMIN    | (semua)   |
| `admin_nas` | ADMIN NASIONAL | 1         |
| `admin_are` | ADMIN AREA     | 2         |
| `admin_reg` | ADMIN REGIONAL | 3         |
| `admin_dis` | ADMIN DISTRICT | 4         |

---

# BAGIAN A — Gating Menu Administration (super-admin only)

### Tujuan
Menu **Administration** (User/Role/Organization/Location Management) saat ini di-hardcode di sidebar dan tampil untuk semua user. Ubah agar **hanya role `admin_sup`** yang bisa melihatnya. Role lain (`admin_nas`/`admin_are`/`admin_reg`/`admin_dis`) tidak melihat menu ini sama sekali. Menu **Dashboard** dan **Assessment** tetap tampil untuk semua role.

> Backend untuk route Administration **sudah** terlindungi middleware `admin` (hanya `admin_sup`), jadi ini murni perbaikan agar UI tidak menampilkan menu yang toh akan ditolak. Tetap tambahkan juga route guard frontend (Tahap A.3) sebagai pengaman tambahan.

### Tahap A.1 — Backend: sertakan relasi `role` di response auth

Saat ini `login` & `me` mengembalikan user **tanpa** relasi role, sehingga frontend tidak tahu `sname` role-nya. Tambahkan `->load('role')`.

Di [backend/app/Http/Controllers/Api/AuthController.php](backend/app/Http/Controllers/Api/AuthController.php):

```php
// method login(), saat menyusun response:
return $this->success([
    'user' => $user->load('role'),   // <-- tambahkan ->load('role')
    'token' => $token,
], 'Login successful.');

// method me():
public function me(Request $request)
{
    return $this->success($request->user()->load('role'), 'Authenticated user retrieved.');
}
```

> Pastikan registrasi (`register`) juga `->load('role')` kalau response-nya menyertakan `user`, supaya konsisten.

### Tahap A.2 — Frontend: helper role + sembunyikan menu

**A.2.1** — Tambahkan helper di [frontend/src/authutils.js](frontend/src/authutils.js) (di dalam class `LaravelAuthBackend`):

```js
/** Mengembalikan sname role user login, atau null. */
getRoleSname() {
    const user = this.getAuthenticatedUser();
    return user?.role?.sname ?? null;
}

isSuperAdmin() {
    return this.getRoleSname() === 'admin_sup';
}
```

**A.2.2** — Di [frontend/src/components/sidebar.vue](frontend/src/components/sidebar.vue), tambahkan computed `isSuperAdmin`, lalu bungkus blok menu Administration dengan `v-if`.

Tambahkan di bagian `computed`:
```js
import { getAuthBackend } from "@/authutils";
// ...
computed: {
    // ... computed yang sudah ada (layoutType) ...
    isSuperAdmin() {
        return getAuthBackend()?.isSuperAdmin() === true;
    },
},
```

Lalu pada `<li class="pc-item pc-hasmenu">` yang membungkus menu **Administration** (blok `#pcAdministration`, sekitar baris 230-255), tambahkan `v-if="isSuperAdmin"`:
```html
<li class="pc-item pc-hasmenu" v-if="isSuperAdmin">
    <BLink class="pc-link" data-bs-toggle="collapse" href="#pcAdministration" ...>
        ...Administration...
    </BLink>
    <div class="collapse" id="pcAdministration"> ... </div>
</li>
```

> Catatan: `getAuthBackend()` baru terisi setelah `initAuthBackend()` dipanggil (biasanya di `main.js`/login flow). Kalau ternyata `getAuthBackend()` mengembalikan null saat sidebar pertama render, fallback baca langsung: `JSON.parse(sessionStorage.getItem('authUser'))?.role?.sname === 'admin_sup'`. Gunakan pola yang konsisten dengan kode auth yang sudah ada.

### Tahap A.3 — Frontend: route guard (pengaman tambahan)

Di [frontend/src/router/index.js](frontend/src/router/index.js), pada `beforeEach` yang sudah ada (cek `authToken`), tambahkan pengecekan: kalau route termasuk grup Administration (path `/users`, `/roles`, `/organizations`, `/locations`) dan user **bukan** `admin_sup`, redirect ke `/dashboard`.

```js
const ADMIN_ONLY_PATHS = ['/users', '/roles', '/organizations', '/locations'];

router.beforeEach((to, from, next) => {
    const isAuthenticated = !!sessionStorage.getItem('authToken');
    // ... logika auth yang sudah ada ...

    if (isAuthenticated && ADMIN_ONLY_PATHS.includes(to.path)) {
        const sname = JSON.parse(sessionStorage.getItem('authUser') || '{}')?.role?.sname;
        if (sname !== 'admin_sup') {
            return next({ path: '/dashboard' });
        }
    }
    // ... lanjutkan next() seperti semula ...
});
```

> Sesuaikan dengan struktur `beforeEach` yang sudah ada — **jangan** menghapus logika auth yang sudah berjalan, cukup tambahkan blok pengecekan admin path ini sebelum `next()`.

### Verifikasi BAGIAN A
1. Login `admin_sup` → menu Administration tampil; bisa buka `/users`.
2. Login `admin_dis` (atau role non-super lain) → menu Administration **tidak tampil**; akses langsung ke `/users` via URL → ter-redirect ke `/dashboard`.
3. Menu Dashboard & Assessment tetap tampil untuk semua role.

---

# BAGIAN B — Fitur Self Assessment

## Tujuan Fitur

Self Assessment dipakai **District Manager** (`admin_dis`) untuk menilai sejauh mana district-nya menjadi "Prima District", berdasarkan pertanyaan terstruktur **Domain → Practice Area → Scope**. Tiap Scope punya 1 pertanyaan & 5 kriteria progresif (A→E). Atasan district (`admin_are`/`admin_reg`/`admin_nas`/`admin_sup`) bisa **melihat** (read-only) hasil district di bawah hierarkinya, tapi tidak mengisi.

## Sumber Data Master

Data lengkap (43 baris) sudah diekstrak langsung dari Excel sumber (`20260608_table_maping_primakelola.xlsx`, sheet `LENGKAP`) dan disimpan sebagai JSON di:

[backend/database/seeders/data/self_assessment_master.json](backend/database/seeders/data/self_assessment_master.json)

**JANGAN ketik ulang / edit manual.** Sudah diverifikasi: 43 baris, semuanya punya tepat 5 kriteria (A-E). Seeder hanya **membaca** file ini. Cakupan: 4 domain — LEADERSHIP & STRATEGIC, POLICY & GUIDENCE, RESOURCE MANAGEMENT, INFRA HEALTHINESS (ejaan mengikuti file sumber apa adanya).

Struktur tiap baris:
```json
{
  "row": 1,
  "domain": "LEADERSHIP & STRATEGIC",
  "weight_domain": null,
  "references": null,
  "practice_area": "STRATEGIC APPROACH",
  "weight_practice_area": null,
  "scope": "Alignment terhadap Target Area dan Regional",
  "perangkat": "-",
  "question": "Apakah District Manager memiliki target district yang selaras dengan target Area/Regional?",
  "criteria": { "A": "...", "B": "...", "C": "...", "D": "...", "E": "..." },
  "max_score": 5
}
```

> **Catatan data:**
> - `weight_domain`, `weight_practice_area`, `references` masih `null` di semua baris — biarkan null untuk sekarang.
> - Baris `row: 27` (INFRA HEALTHINESS / BIR) punya `scope: null` (lanjutan pertanyaan). Kalau `scope` kosong, tampilkan `question` saja tanpa judul scope — **normal, bukan data rusak**.

## Keputusan Desain (baca sebelum coding)

1. **3 tabel baru, flat.** `domain`/`practice_area`/`scope` disimpan string langsung di `assessment_questions` (bukan FK ke tabel master). Tidak perlu tabel master domain terpisah.
2. **Scoring**: `achieved_level` 1 huruf (`A`-`E`) → angka (`A`=1 … `E`=5). Skor total = `(jumlah level terkonversi / jumlah max_score) × 100`.
3. **Akses — PENTING, BERBEDA DARI POLA LAIN:**
   - Middleware `admin` (`EnsureUserIsAdmin`) **hanya** mengizinkan `admin_sup`. Self Assessment **TIDAK BOLEH** di belakang middleware ini — yang mengisi `admin_dis`.
   - Semua route Self Assessment pakai `auth:sanctum`; **otorisasi dicek manual di controller**.
   - `admin_dis` hanya boleh CRUD self assessment `organization_id` miliknya. Role di atasnya read-only & dibatasi descendant via `organization_mapping`; `admin_sup` lihat semua.
4. **Periode = per Kuartal.** Format `"<TAHUN>-Q<n>"` dengan n ∈ {1,2,3,4}, contoh `"2026-Q3"`. Divalidasi regex di backend & dipilih lewat dropdown (tahun + kuartal) di frontend. Unik per `organization_id` + `period`.
5. **Status: `open` → `draft` → `submitted`.**
   - `open`: record periode baru dibuat, belum ada jawaban tersimpan.
   - `draft`: sudah pernah disimpan (saveAnswers), masih bisa diedit.
   - `submitted`: final, terkunci (tidak bisa diedit), skor dihitung.
   - Transisi: `store()` membuat status `open`; `saveAnswers()` mengubah `open`→`draft` (boleh kalau status ≠ `submitted`); `submit()` mengubah `open`/`draft`→`submitted`.
6. **Evidence = catatan teks + upload file/foto.** Tiap jawaban punya `evidence_note` (teks) dan `evidence_file` (path file di disk `public`; jpg/png/pdf).

---

## TAHAP B.1 — Migration tabel master `assessment_questions`

Buat `backend/database/migrations/2026_06_29_100000_create_assessment_questions_table.php` (sesuaikan prefix timestamp):

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessment_questions', function (Blueprint $table) {
            $table->id('assessment_question_id');
            $table->string('domain');
            $table->decimal('weight_domain', 5, 2)->nullable();
            $table->string('references')->nullable();
            $table->string('practice_area');
            $table->decimal('weight_practice_area', 5, 2)->nullable();
            $table->string('scope')->nullable();
            $table->string('perangkat')->nullable();
            $table->text('question');
            $table->text('criteria_a');
            $table->text('criteria_b');
            $table->text('criteria_c');
            $table->text('criteria_d');
            $table->text('criteria_e');
            $table->unsignedTinyInteger('max_score')->default(5);
            $table->unsignedInteger('sort_order')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessment_questions');
    }
};
```

---

## TAHAP B.2 — Model + Seeder data master

**B.2.1 — `backend/app/Models/AssessmentQuestion.php`:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'domain', 'weight_domain', 'references', 'practice_area', 'weight_practice_area',
    'scope', 'perangkat', 'question',
    'criteria_a', 'criteria_b', 'criteria_c', 'criteria_d', 'criteria_e',
    'max_score', 'sort_order',
])]
class AssessmentQuestion extends Model
{
    protected $primaryKey = 'assessment_question_id';

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'assessment_question_id', 'assessment_question_id');
    }
}
```

**B.2.2 — `backend/database/seeders/AssessmentQuestionSeeder.php`** (idempoten, key `sort_order`):
```php
<?php

namespace Database\Seeders;

use App\Models\AssessmentQuestion;
use Illuminate\Database\Seeder;

class AssessmentQuestionSeeder extends Seeder
{
    public function run(): void
    {
        $path = database_path('seeders/data/self_assessment_master.json');
        $rows = json_decode(file_get_contents($path), true);

        foreach ($rows as $row) {
            AssessmentQuestion::updateOrCreate(
                ['sort_order' => $row['row']],
                [
                    'domain' => $row['domain'],
                    'weight_domain' => $row['weight_domain'],
                    'references' => $row['references'],
                    'practice_area' => $row['practice_area'],
                    'weight_practice_area' => $row['weight_practice_area'],
                    'scope' => $row['scope'],
                    'perangkat' => $row['perangkat'],
                    'question' => $row['question'],
                    'criteria_a' => $row['criteria']['A'],
                    'criteria_b' => $row['criteria']['B'],
                    'criteria_c' => $row['criteria']['C'],
                    'criteria_d' => $row['criteria']['D'],
                    'criteria_e' => $row['criteria']['E'],
                    'max_score' => $row['max_score'],
                ]
            );
        }
    }
}
```

**B.2.3 — Daftarkan di [backend/database/seeders/DatabaseSeeder.php](backend/database/seeders/DatabaseSeeder.php):**
```php
$this->call(AssessmentQuestionSeeder::class);
```

**Checklist:** setelah seeding harus ada **43 baris**; `updateOrCreate` aman diulang.

---

## TAHAP B.3 — Migration tabel transaksi

**B.3.1 — `..._create_self_assessments_table.php`** (header per organisasi + periode):
```php
Schema::create('self_assessments', function (Blueprint $table) {
    $table->id('self_assessment_id');
    $table->foreignId('organization_id')
        ->constrained('organizations', 'organization_id')->cascadeOnDelete();
    $table->string('period'); // contoh: "2026-Q3"
    $table->enum('status', ['open', 'draft', 'submitted'])->default('open');
    $table->foreignId('submitted_by')->nullable()
        ->constrained('users', 'user_id')->nullOnDelete();
    $table->timestamp('submitted_at')->nullable();
    $table->decimal('total_score', 6, 2)->nullable();
    $table->foreignId('created_by')->nullable()
        ->constrained('users', 'user_id')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()
        ->constrained('users', 'user_id')->nullOnDelete();
    $table->timestamps();

    $table->unique(['organization_id', 'period']);
});
```

**B.3.2 — `..._create_self_assessment_answers_table.php`** (jawaban per pertanyaan, dengan evidence note + file):
```php
Schema::create('self_assessment_answers', function (Blueprint $table) {
    $table->id('self_assessment_answer_id');
    $table->foreignId('self_assessment_id')
        ->constrained('self_assessments', 'self_assessment_id')->cascadeOnDelete();
    $table->foreignId('assessment_question_id')
        ->constrained('assessment_questions', 'assessment_question_id')->cascadeOnDelete();
    $table->enum('achieved_level', ['A', 'B', 'C', 'D', 'E'])->nullable();
    $table->text('evidence_note')->nullable();
    $table->string('evidence_file')->nullable(); // path di disk 'public'
    $table->foreignId('created_by')->nullable()
        ->constrained('users', 'user_id')->nullOnDelete();
    $table->foreignId('updated_by')->nullable()
        ->constrained('users', 'user_id')->nullOnDelete();
    $table->timestamps();

    $table->unique(['self_assessment_id', 'assessment_question_id']);
});
```

> Urutan migration: `assessment_questions` (B.1) → `self_assessments` (B.3.1) → `self_assessment_answers` (B.3.2), karena FK.

---

## TAHAP B.4 — Setup storage untuk upload file

1. Jalankan sekali: `cd backend && php artisan storage:link` (membuat symlink `public/storage` → `storage/app/public`).
2. File evidence disimpan di disk `public` (folder `storage/app/public/evidence/...`) agar bisa diakses via URL. **Tidak perlu** mengubah `FILESYSTEM_DISK` default; cukup panggil disk `public` secara eksplisit di controller (`Storage::disk('public')`).

---

## TAHAP B.5 — Model transaksi

**B.5.1 — `backend/app/Models/SelfAssessment.php`** (pola `booted()` mengikuti [Organization.php](backend/app/Models/Organization.php)):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

#[Fillable(['organization_id', 'period', 'status', 'submitted_by', 'submitted_at', 'total_score'])]
class SelfAssessment extends Model
{
    protected $primaryKey = 'self_assessment_id';

    protected static function booted(): void
    {
        static::creating(function (SelfAssessment $a) {
            if (Auth::check()) {
                $a->created_by ??= Auth::id();
                $a->updated_by ??= Auth::id();
            }
        });
        static::updating(function (SelfAssessment $a) {
            if (Auth::check()) {
                $a->updated_by = Auth::id();
            }
        });
    }

    protected function casts(): array
    {
        return ['submitted_at' => 'datetime', 'total_score' => 'decimal:2'];
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class, 'organization_id', 'organization_id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by', 'user_id');
    }

    public function answers(): HasMany
    {
        return $this->hasMany(SelfAssessmentAnswer::class, 'self_assessment_id', 'self_assessment_id');
    }
}
```

**B.5.2 — `backend/app/Models/SelfAssessmentAnswer.php`** (dengan accessor URL file evidence):
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable(['self_assessment_id', 'assessment_question_id', 'achieved_level', 'evidence_note', 'evidence_file'])]
class SelfAssessmentAnswer extends Model
{
    protected $primaryKey = 'self_assessment_answer_id';

    protected $appends = ['evidence_file_url'];

    protected function evidenceFileUrl(): Attribute
    {
        return Attribute::get(fn () => $this->evidence_file
            ? Storage::disk('public')->url($this->evidence_file)
            : null);
    }

    public function selfAssessment(): BelongsTo
    {
        return $this->belongsTo(SelfAssessment::class, 'self_assessment_id', 'self_assessment_id');
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(AssessmentQuestion::class, 'assessment_question_id', 'assessment_question_id');
    }
}
```

---

## TAHAP B.6 — Controllers

**B.6.1 — `backend/app/Http/Controllers/Api/AssessmentQuestionController.php`** (master, read-only):
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Traits\ApiResponse;

class AssessmentQuestionController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success(AssessmentQuestion::orderBy('sort_order')->get());
    }
}
```

**B.6.2 — `backend/app/Http/Controllers/Api/SelfAssessmentController.php`** (otorisasi manual; status open/draft/submitted; upload evidence):
```php
<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AssessmentQuestion;
use App\Models\Organization;
use App\Models\OrganizationMapping;
use App\Models\SelfAssessment;
use App\Models\SelfAssessmentAnswer;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SelfAssessmentController extends Controller
{
    use ApiResponse;

    /** organization_id yang boleh DILIHAT user ini. */
    private function visibleOrganizationIds(User $user): array
    {
        if ($user->role?->sname === 'admin_sup') {
            return Organization::pluck('organization_id')->all();
        }
        return OrganizationMapping::where('ancestor_id', $user->organization_id)
            ->pluck('descendant_id')->all();
    }

    private function isDistrictUser(User $user): bool
    {
        return $user->role?->sname === 'admin_dis';
    }

    /** Pastikan user adalah pemilik district & assessment masih bisa diedit. */
    private function ensureEditable(User $user, SelfAssessment $a): ?\Illuminate\Http\JsonResponse
    {
        if (! $this->isDistrictUser($user) || $a->organization_id !== $user->organization_id) {
            return $this->error('Forbidden.', 403);
        }
        if ($a->status === 'submitted') {
            return $this->error('Self assessment sudah disubmit dan tidak dapat diubah.', 422);
        }
        return null;
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $list = SelfAssessment::with('organization', 'submittedBy')
            ->whereIn('organization_id', $this->visibleOrganizationIds($user))
            ->when($request->query('period'), fn ($q, $p) => $q->where('period', $p))
            ->orderByDesc('self_assessment_id')
            ->get();

        return $this->success($list);
    }

    /** Get-or-create record periode untuk organisasi milik user (district saja). Status awal 'open'. */
    public function store(Request $request)
    {
        $user = $request->user();
        if (! $this->isDistrictUser($user) || ! $user->organization_id) {
            return $this->error('Hanya District Manager yang dapat membuat self assessment.', 403);
        }

        $validated = $request->validate([
            'period' => ['required', 'string', 'regex:/^\d{4}-Q[1-4]$/'],
        ]);

        $assessment = SelfAssessment::firstOrCreate(
            ['organization_id' => $user->organization_id, 'period' => $validated['period']],
            ['status' => 'open']
        );

        return $this->success($assessment->load('answers'), 'Self assessment siap diisi.', 201);
    }

    public function show(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();
        if (! in_array($selfAssessment->organization_id, $this->visibleOrganizationIds($user))) {
            return $this->error('Forbidden.', 403);
        }
        return $this->success(
            $selfAssessment->load('organization', 'submittedBy', 'answers.question')
        );
    }

    /** Simpan/update jawaban (bulk, JSON). open -> draft. */
    public function saveAnswers(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();
        if ($resp = $this->ensureEditable($user, $selfAssessment)) {
            return $resp;
        }

        $validated = $request->validate([
            'answers' => ['required', 'array'],
            'answers.*.assessment_question_id' => ['required', 'exists:assessment_questions,assessment_question_id'],
            'answers.*.achieved_level' => ['nullable', 'in:A,B,C,D,E'],
            'answers.*.evidence_note' => ['nullable', 'string'],
        ]);

        foreach ($validated['answers'] as $answer) {
            SelfAssessmentAnswer::updateOrCreate(
                [
                    'self_assessment_id' => $selfAssessment->getKey(),
                    'assessment_question_id' => $answer['assessment_question_id'],
                ],
                [
                    'achieved_level' => $answer['achieved_level'] ?? null,
                    'evidence_note' => $answer['evidence_note'] ?? null,
                ]
            );
        }

        if ($selfAssessment->status === 'open') {
            $selfAssessment->update(['status' => 'draft']);
        }

        return $this->success($selfAssessment->load('answers'), 'Jawaban disimpan.');
    }

    /** Upload file evidence untuk 1 jawaban (multipart, field 'file'). */
    public function uploadEvidence(Request $request, SelfAssessment $selfAssessment, AssessmentQuestion $assessmentQuestion)
    {
        $user = $request->user();
        if ($resp = $this->ensureEditable($user, $selfAssessment)) {
            return $resp;
        }

        $request->validate([
            'file' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'], // max 5MB
        ]);

        $answer = SelfAssessmentAnswer::firstOrNew([
            'self_assessment_id' => $selfAssessment->getKey(),
            'assessment_question_id' => $assessmentQuestion->getKey(),
        ]);

        // hapus file lama kalau ada (ganti file)
        if ($answer->evidence_file) {
            Storage::disk('public')->delete($answer->evidence_file);
        }

        $path = $request->file('file')->store(
            'evidence/'.$selfAssessment->getKey(), 'public'
        );
        $answer->evidence_file = $path;
        $answer->save();

        if ($selfAssessment->status === 'open') {
            $selfAssessment->update(['status' => 'draft']);
        }

        return $this->success($answer->fresh(), 'File evidence diupload.');
    }

    /** Submit & hitung skor akhir. Boleh dari open/draft. Setelah ini terkunci. */
    public function submit(Request $request, SelfAssessment $selfAssessment)
    {
        $user = $request->user();
        if (! $this->isDistrictUser($user) || $selfAssessment->organization_id !== $user->organization_id) {
            return $this->error('Forbidden.', 403);
        }
        if ($selfAssessment->status === 'submitted') {
            return $this->error('Self assessment sudah disubmit sebelumnya.', 422);
        }

        $levelToScore = ['A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5];
        $answers = $selfAssessment->answers()->with('question')->get();

        $totalMax = $answers->sum(fn ($a) => $a->question->max_score);
        $totalScore = $answers->sum(fn ($a) => $levelToScore[$a->achieved_level] ?? 0);

        $selfAssessment->update([
            'status' => 'submitted',
            'submitted_by' => $user->getKey(),
            'submitted_at' => now(),
            'total_score' => $totalMax > 0 ? round($totalScore / $totalMax * 100, 2) : 0,
        ]);

        return $this->success($selfAssessment->fresh(), 'Self assessment berhasil disubmit.');
    }
}
```

> **Jangan** menambahkan aturan "semua pertanyaan wajib terjawab sebelum submit" — di luar scope. Pertanyaan kosong dihitung skor 0.

---

## TAHAP B.7 — Registrasi route

Di [backend/routes/api.php](backend/routes/api.php), **di dalam grup `auth:sanctum` tapi DI LUAR grup `admin`**:
```php
use App\Http\Controllers\Api\AssessmentQuestionController;
use App\Http\Controllers\Api\SelfAssessmentController;

Route::middleware('auth:sanctum')->group(function () {
    // ... route auth yang sudah ada (/user, /logout) ...

    Route::get('assessment-questions', [AssessmentQuestionController::class, 'index']);

    Route::get('self-assessments', [SelfAssessmentController::class, 'index']);
    Route::post('self-assessments', [SelfAssessmentController::class, 'store']);
    Route::get('self-assessments/{selfAssessment}', [SelfAssessmentController::class, 'show']);
    Route::put('self-assessments/{selfAssessment}/answers', [SelfAssessmentController::class, 'saveAnswers']);
    Route::post('self-assessments/{selfAssessment}/questions/{assessmentQuestion}/evidence', [SelfAssessmentController::class, 'uploadEvidence']);
    Route::post('self-assessments/{selfAssessment}/submit', [SelfAssessmentController::class, 'submit']);

    Route::middleware('admin')->group(function () {
        // ... route admin-only yang sudah ada — JANGAN diubah ...
    });
});
```

---

## TAHAP B.8 — Frontend: route

Di [frontend/src/router/routes.js](frontend/src/router/routes.js), route `/assessment/self` sudah ada tapi masih pakai `placeholder.vue`. Ganti komponennya:
```js
{
    path: "/assessment/self",
    name: "assessment-self",
    meta: { title: "Self Assessment", group: "Assessment" },
    component: () => import("../views/pages/self-assessment.vue"),
},
```
> `/assessment/on-desk` & `/assessment/on-site` tetap placeholder (di luar scope).

---

## TAHAP B.9 — Frontend: halaman `frontend/src/views/pages/self-assessment.vue`

Pola seperti [users.vue](frontend/src/views/pages/users.vue): Options API, `api` dari `@/services/api`, bungkus `Layout` + `pageheader`.

**Kerangka `<script>`:**
```js
import api from "@/services/api";
import Layout from "@/layout/main.vue";
import pageheader from "@/components/page-header.vue";

export default {
    name: "SelfAssessment",
    components: { Layout, pageheader },
    data() {
        const now = new Date();
        return {
            loading: false,
            saving: false,
            year: now.getFullYear(),
            quarter: Math.floor(now.getMonth() / 3) + 1, // 1..4
            years: [now.getFullYear() - 1, now.getFullYear(), now.getFullYear() + 1],
            assessment: null,   // hasil POST /self-assessments
            questions: [],      // hasil GET /assessment-questions
            answers: {},        // map: question_id -> { achieved_level, evidence_note, evidence_file_url }
            errorMsg: "",
        };
    },
    computed: {
        period() {
            return `${this.year}-Q${this.quarter}`;
        },
        isReadOnly() {
            return this.assessment?.status === "submitted";
        },
        statusLabel() {
            return { open: "Open", draft: "Draft", submitted: "Submitted" }[this.assessment?.status] || "-";
        },
        // { [domain]: { [practice_area]: [question, ...] } }, urutan ikut sort_order
        groupedQuestions() {
            const groups = {};
            for (const q of this.questions) {
                groups[q.domain] = groups[q.domain] || {};
                groups[q.domain][q.practice_area] = groups[q.domain][q.practice_area] || [];
                groups[q.domain][q.practice_area].push(q);
            }
            return groups;
        },
    },
    methods: {
        criteriaText(q, level) {
            return q["criteria_" + level.toLowerCase()];
        },
        async fetchQuestions() {
            const { data } = await api.get("/assessment-questions");
            this.questions = data.data;
        },
        async initAssessment() {
            const { data } = await api.post("/self-assessments", { period: this.period });
            this.assessment = data.data;
            this.answers = {};
            for (const q of this.questions) {
                this.$set(this.answers, q.assessment_question_id, {
                    achieved_level: null, evidence_note: "", evidence_file_url: null,
                });
            }
            for (const ans of this.assessment.answers || []) {
                this.$set(this.answers, ans.assessment_question_id, {
                    achieved_level: ans.achieved_level,
                    evidence_note: ans.evidence_note,
                    evidence_file_url: ans.evidence_file_url || null,
                });
            }
        },
        buildPayload() {
            return Object.entries(this.answers).map(([qid, val]) => ({
                assessment_question_id: Number(qid),
                achieved_level: val.achieved_level || null,
                evidence_note: val.evidence_note || null,
            }));
        },
        async saveDraft() {
            this.saving = true;
            try {
                await api.put(`/self-assessments/${this.assessment.self_assessment_id}/answers`,
                    { answers: this.buildPayload() });
                await this.refresh();
            } catch (e) {
                this.errorMsg = e.response?.data?.message || "Gagal menyimpan.";
            } finally {
                this.saving = false;
            }
        },
        async uploadEvidence(question, event) {
            const file = event.target.files[0];
            if (!file) return;
            const fd = new FormData();
            fd.append("file", file);
            try {
                const { data } = await api.post(
                    `/self-assessments/${this.assessment.self_assessment_id}/questions/${question.assessment_question_id}/evidence`,
                    fd, { headers: { "Content-Type": "multipart/form-data" } });
                this.answers[question.assessment_question_id].evidence_file_url = data.data.evidence_file_url;
            } catch (e) {
                this.errorMsg = e.response?.data?.message || "Gagal upload file.";
            }
        },
        async submitAssessment() {
            if (!confirm("Setelah disubmit, jawaban tidak dapat diubah lagi. Lanjutkan?")) return;
            this.saving = true;
            try {
                await this.saveDraft();
                await api.post(`/self-assessments/${this.assessment.self_assessment_id}/submit`);
                await this.refresh();
            } catch (e) {
                this.errorMsg = e.response?.data?.message || "Gagal submit.";
            } finally {
                this.saving = false;
            }
        },
        async refresh() {
            const { data } = await api.get(`/self-assessments/${this.assessment.self_assessment_id}`);
            this.assessment = data.data;
        },
        async reloadForPeriod() {
            this.loading = true;
            this.errorMsg = "";
            try {
                await this.initAssessment();
            } catch (e) {
                this.errorMsg = e.response?.data?.message || "Gagal memuat periode.";
            } finally {
                this.loading = false;
            }
        },
    },
    async mounted() {
        this.loading = true;
        try {
            await this.fetchQuestions();
            await this.initAssessment();
        } catch (e) {
            this.errorMsg = e.response?.data?.message || "Gagal memuat self assessment.";
        } finally {
            this.loading = false;
        }
    },
};
```

**Kerangka `<template>`** (sesuaikan styling dengan komponen Bootstrap-Vue yang sudah dipakai):
- `pageheader` judul "Self Assessment".
- Baris atas: dropdown **Tahun** (`v-model="year"`, opsi dari `years`) + dropdown **Kuartal** (`v-model="quarter"`, opsi 1-4 berlabel `Q1..Q4`) + tombol "Muat" (`@click="reloadForPeriod"`). Tampilkan badge `statusLabel`.
- Render `groupedQuestions` sebagai accordion: **Domain** → **Practice Area** → tiap pertanyaan.
- Tiap pertanyaan:
  - Tampilkan `scope` sebagai judul (kalau ada; kalau `null` lewati judul), lalu `question`.
  - 5 radio `A`-`E`, label = `criteriaText(q,'A')..('E')`, `v-model="answers[q.assessment_question_id].achieved_level"`, `:value="'A'"` dst.
  - Textarea `v-model="answers[q.assessment_question_id].evidence_note"`.
  - Input file: `<input type="file" accept=".jpg,.jpeg,.png,.pdf" @change="uploadEvidence(q, $event)" :disabled="isReadOnly">`; kalau `answers[q...].evidence_file_url` ada, tampilkan link "Lihat file" ke URL tersebut.
  - Semua input `:disabled="isReadOnly"`.
- Tombol "Simpan Draft" (`@click="saveDraft"`) & "Submit" (`@click="submitAssessment"`) — sembunyikan saat `isReadOnly`.
- Saat `isReadOnly`, tampilkan `assessment.total_score`.
- Tampilkan `errorMsg` bila ada.

> Catatan: file evidence diupload **langsung saat dipilih** (endpoint terpisah), terlepas dari tombol "Simpan Draft" (yang menyimpan level + note). Ini disengaja agar upload multipart tidak tercampur dengan bulk-save JSON.

---

## Verifikasi (wajib)

**Backend:**
1. `cd backend && php artisan migrate` (atau `migrate:fresh --seed`) → 3 tabel baru tanpa error.
2. `php artisan storage:link` → symlink `public/storage` terbuat.
3. Tinker: `\App\Models\AssessmentQuestion::count()` = **43**; `whereNull('criteria_e')->count()` = 0.
4. Jalankan seeder dua kali → tetap 43 (idempoten).

**API / otorisasi** (user dummy dari `DummyUserSeeder`, password `password`):
5. Login `admin_dis` → `POST /api/self-assessments {"period":"2026-Q3"}` → status `open`. `PUT .../answers` → status berubah `draft`, jawaban tersimpan. Upload file ke `.../questions/{id}/evidence` → `evidence_file_url` terisi. `POST .../submit` → `submitted` + `total_score`.
6. `PUT .../answers` atau upload setelah submit → 422.
7. `POST /api/self-assessments {"period":"2026-Q5"}` → 422 (regex periode menolak).
8. `admin_dis` akses assessment district lain → 403.
9. `admin_are`/`admin_reg` → `GET /api/self-assessments` hanya district di bawahnya; POST/PUT ditolak 403.
10. `admin_sup` → `GET /api/self-assessments` semua organisasi.

**Frontend:**
11. BAGIAN A: login `admin_sup` → menu Administration tampil; login `admin_dis` → tidak tampil; akses `/users` langsung → redirect `/dashboard`.
12. `php artisan serve` + `npm run serve`. Login `admin_dis` → `/assessment/self`: pilih tahun+kuartal → muat; isi A-E, note, upload file; "Simpan Draft" (status jadi Draft); "Submit" (konfirmasi) mengunci form + tampil skor.

---

## Ringkasan File yang Disentuh

| File | Aksi |
|------|------|
| **BAGIAN A** | |
| [backend/app/Http/Controllers/Api/AuthController.php](backend/app/Http/Controllers/Api/AuthController.php) | `->load('role')` di login/me (& register bila perlu) |
| [frontend/src/authutils.js](frontend/src/authutils.js) | Tambah `getRoleSname()` & `isSuperAdmin()` |
| [frontend/src/components/sidebar.vue](frontend/src/components/sidebar.vue) | `v-if="isSuperAdmin"` pada menu Administration + computed |
| [frontend/src/router/index.js](frontend/src/router/index.js) | Route guard untuk path admin-only |
| **BAGIAN B** | |
| `backend/database/seeders/data/self_assessment_master.json` | **Sudah ada** — data master 43 pertanyaan. Jangan diedit manual. |
| `backend/database/migrations/..._create_assessment_questions_table.php` | **Buat baru** (B.1) |
| `backend/database/migrations/..._create_self_assessments_table.php` | **Buat baru** (B.3.1) |
| `backend/database/migrations/..._create_self_assessment_answers_table.php` | **Buat baru** (B.3.2) |
| `backend/app/Models/AssessmentQuestion.php` | **Buat baru** (B.2.1) |
| `backend/app/Models/SelfAssessment.php` | **Buat baru** (B.5.1) |
| `backend/app/Models/SelfAssessmentAnswer.php` | **Buat baru** (B.5.2) |
| `backend/database/seeders/AssessmentQuestionSeeder.php` | **Buat baru** (B.2.2) |
| [backend/database/seeders/DatabaseSeeder.php](backend/database/seeders/DatabaseSeeder.php) | Daftarkan `AssessmentQuestionSeeder` |
| `backend/app/Http/Controllers/Api/AssessmentQuestionController.php` | **Buat baru** (B.6.1) |
| `backend/app/Http/Controllers/Api/SelfAssessmentController.php` | **Buat baru** (B.6.2) |
| [backend/routes/api.php](backend/routes/api.php) | Tambah route assessment di luar grup `admin` (B.7) |
| [frontend/src/router/routes.js](frontend/src/router/routes.js) | Ganti komponen `/assessment/self` ke `self-assessment.vue` (B.8) |
| `frontend/src/views/pages/self-assessment.vue` | **Buat baru** (B.9) |

---

## Hal yang TIDAK Boleh Dilakukan

- **Jangan** memasang route Self Assessment di dalam `Route::middleware('admin')->group(...)` — hanya `admin_sup` yang lolos; district akan kena 403.
- **Jangan** membuat tabel master domain/practice-area terpisah — flat di `assessment_questions`.
- **Jangan** mengetik ulang / mengedit data pertanyaan manual — selalu dari `self_assessment_master.json`. Revisi data = update JSON lalu jalankan ulang seeder.
- **Jangan** menambahkan workflow approval/multi-level review — di luar scope.
- **Jangan** mengubah `OrganizationMappingService`, struktur primary key, atau route/middleware admin yang sudah ada.
- **Jangan** menyimpan file evidence di disk `local` privat — pakai disk `public` agar bisa diakses via URL (sudah `storage:link`).
- Pertahankan pola `created_by`/`updated_by` via `booted()` seperti model lain.
