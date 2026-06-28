<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\OrganizationType;
use App\Services\OrganizationMappingService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrganizationSeeder extends Seeder
{
    /**
     * Maps the level label used in the raw data below to the `name` column
     * of `organization_types` (seeded by OrganizationTypeSeeder).
     */
    private const LEVEL_TO_TYPE = [
        'NASIONAL' => 'National',
        'AREA' => 'Area',
        'REGIONAL' => 'Regional',
        'DISTRICT' => 'District',
    ];

    /**
     * [name, sname, level, parent name (null = root)].
     *
     * Order matters: every parent must appear before its children so the
     * name -> id lookup below can resolve `parent_organization_id`.
     */
    private const ROWS = [
        ['TELKOM INFRASTUKTUR INDONESIA', 'tif', 'NASIONAL', null],

        ['AREA III - JAWA BALI', 'tif3', 'AREA', 'TELKOM INFRASTUKTUR INDONESIA'],
        ['AREA II - JABODETABEK JABAR', 'tif2', 'AREA', 'TELKOM INFRASTUKTUR INDONESIA'],
        ['AREA I - SUMATERA', 'tif1', 'AREA', 'TELKOM INFRASTUKTUR INDONESIA'],
        ['AREA IV - PAMASUKA', 'tif4', 'AREA', 'TELKOM INFRASTUKTUR INDONESIA'],

        ['REGIONAL BALI NUSRA', 'banu', 'REGIONAL', 'AREA III - JAWA BALI'],
        ['REGIONAL EASTERN JABOTABEK', 'esja', 'REGIONAL', 'AREA II - JABODETABEK JABAR'],
        ['REGIONAL JAKARTA BANTEN', 'jkbn', 'REGIONAL', 'AREA II - JABODETABEK JABAR'],
        ['REGIONAL JATENG DIY', 'jadi', 'REGIONAL', 'AREA III - JAWA BALI'],
        ['REGIONAL JATIM', 'jati', 'REGIONAL', 'AREA III - JAWA BALI'],
        ['REGIONAL JAWA BARAT', 'jaba', 'REGIONAL', 'AREA II - JABODETABEK JABAR'],
        ['REGIONAL KALIMANTAN', 'klmn', 'REGIONAL', 'AREA IV - PAMASUKA'],
        ['REGIONAL MALUKU PAPUA', 'puma', 'REGIONAL', 'AREA IV - PAMASUKA'],
        ['REGIONAL SULAWESI', 'sula', 'REGIONAL', 'AREA IV - PAMASUKA'],
        ['REGIONAL SUMBAGSEL', 'smsl', 'REGIONAL', 'AREA I - SUMATERA'],
        ['REGIONAL SUMBAGTENG', 'smtg', 'REGIONAL', 'AREA I - SUMATERA'],
        ['REGIONAL SUMBAGUT', 'smut', 'REGIONAL', 'AREA I - SUMATERA'],

        ['AMBON', 'ambn', 'DISTRICT', 'REGIONAL MALUKU PAPUA'],
        ['BALIKPAPAN', 'blpp', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['BANDA ACEH', 'bnda', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['BANDUNG', 'bndg', 'DISTRICT', 'REGIONAL JAWA BARAT'],
        ['BANJARMASIN', 'bnjr', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['BATAM', 'btam', 'DISTRICT', 'REGIONAL SUMBAGTENG'],
        ['BEKASI', 'bksi', 'DISTRICT', 'REGIONAL EASTERN JABOTABEK'],
        ['BENGKULU', 'bngk', 'DISTRICT', 'REGIONAL SUMBAGSEL'],
        ['BINJAI', 'binj', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['BOGOR & SUKABUMI', 'bgrs', 'DISTRICT', 'REGIONAL EASTERN JABOTABEK'],
        ['BONE', 'bone', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['BUKIT TINGGI', 'bktt', 'DISTRICT', 'REGIONAL SUMBAGTENG'],
        ['CIREBON', 'cirb', 'DISTRICT', 'REGIONAL JAWA BARAT'],
        ['DENPASAR', 'dnps', 'DISTRICT', 'REGIONAL BALI NUSRA'],
        ['DUMAI', 'dmai', 'DISTRICT', 'REGIONAL SUMBAGTENG'],
        ['FLORES', 'flrs', 'DISTRICT', 'REGIONAL BALI NUSRA'],
        ['JAMBI', 'jamb', 'DISTRICT', 'REGIONAL SUMBAGSEL'],
        ['JAYAPURA', 'jypr', 'DISTRICT', 'REGIONAL MALUKU PAPUA'],
        ['JEMBER', 'jmbr', 'DISTRICT', 'REGIONAL JATIM'],
        ['KARAWANG', 'krwa', 'DISTRICT', 'REGIONAL EASTERN JABOTABEK'],
        ['KENDARI', 'kndr', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['KUPANG', 'kpng', 'DISTRICT', 'REGIONAL BALI NUSRA'],
        ['LAMONGAN', 'lmng', 'DISTRICT', 'REGIONAL JATIM'],
        ['LAMPUNG', 'lmpu', 'DISTRICT', 'REGIONAL SUMBAGSEL'],
        ['MADIUN', 'mdun', 'DISTRICT', 'REGIONAL JATIM'],
        ['MAGELANG', 'mage', 'DISTRICT', 'REGIONAL JATENG DIY'],
        ['MAKASSAR', 'mksr', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['MALANG', 'mlng', 'DISTRICT', 'REGIONAL JATIM'],
        ['MANADO', 'mndo', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['MATARAM', 'mtrm', 'DISTRICT', 'REGIONAL BALI NUSRA'],
        ['MEDAN', 'mdan', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['NORTHERN JAKARTA', 'nrtj', 'DISTRICT', 'REGIONAL JAKARTA BANTEN'],
        ['PADANG', 'pdng', 'DISTRICT', 'REGIONAL SUMBAGTENG'],
        ['PADANG SIDEMPUAN', 'pdgs', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['PALANGKARAYA', 'plkr', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['PALEMBANG', 'plmb', 'DISTRICT', 'REGIONAL SUMBAGSEL'],
        ['PALU', 'palu', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['PANGKALAN BUN', 'pkbu', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['PANGKAL PINANG', 'pklp', 'DISTRICT', 'REGIONAL SUMBAGSEL'],
        ['PAREPARE', 'prpr', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['PEKALONGAN', 'pkln', 'DISTRICT', 'REGIONAL JATENG DIY'],
        ['PEKANBARU', 'pknb', 'DISTRICT', 'REGIONAL SUMBAGTENG'],
        ['PEMATANG SIANTAR', 'pmts', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['PONTIANAK', 'pnti', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['PURWOKERTO', 'prwo', 'DISTRICT', 'REGIONAL JATENG DIY'],
        ['RANTAUPRAPAT', 'rnpr', 'DISTRICT', 'REGIONAL SUMBAGUT'],
        ['SAMARINDA', 'smrn', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['SEMARANG', 'smra', 'DISTRICT', 'REGIONAL JATENG DIY'],
        ['SERANG', 'srng', 'DISTRICT', 'REGIONAL JAKARTA BANTEN'],
        ['SIDOARJO', 'sido', 'DISTRICT', 'REGIONAL JATIM'],
        ['SOREANG', 'srea', 'DISTRICT', 'REGIONAL JAWA BARAT'],
        ['SORONG', 'soro', 'DISTRICT', 'REGIONAL MALUKU PAPUA'],
        ['SOUTHERN JAKARTA', 'sthj', 'DISTRICT', 'REGIONAL JAKARTA BANTEN'],
        ['SURAKARTA', 'sura', 'DISTRICT', 'REGIONAL JATENG DIY'],
        ['SURAMADU', 'suma', 'DISTRICT', 'REGIONAL JATIM'],
        ['TANGERANG', 'tngr', 'DISTRICT', 'REGIONAL JAKARTA BANTEN'],
        ['TARAKAN', 'trka', 'DISTRICT', 'REGIONAL KALIMANTAN'],
        ['TASIKMALAYA', 'tsik', 'DISTRICT', 'REGIONAL JAWA BARAT'],
        ['TERNATE', 'trnt', 'DISTRICT', 'REGIONAL SULAWESI'],
        ['TIMIKA', 'tmka', 'DISTRICT', 'REGIONAL MALUKU PAPUA'],
        ['YOGYAKARTA', 'ygya', 'DISTRICT', 'REGIONAL JATENG DIY'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $typeIds = OrganizationType::pluck('organization_type_id', 'name');
        $mapping = app(OrganizationMappingService::class);

        /** @var array<string, int> $idsByName */
        $idsByName = [];

        foreach (self::ROWS as [$name, $sname, $level, $parentName]) {
            $existing = Organization::where('sname', $sname)->first();

            if ($existing) {
                $idsByName[$name] = $existing->organization_id;

                continue;
            }

            $typeName = self::LEVEL_TO_TYPE[$level];
            $parentId = $parentName ? ($idsByName[$parentName] ?? null) : null;

            // DatabaseSeeder uses WithoutModelEvents, so Organization's
            // `creating` event (which normally auto-generates uuid) won't
            // fire here - set it explicitly, same as RoleSeeder /
            // OrganizationTypeSeeder.
            $organization = Organization::forceCreate([
                'uuid' => (string) Str::uuid(),
                'name' => $name,
                'sname' => $sname,
                'organization_type_id' => $typeIds[$typeName],
                'parent_organization_id' => $parentId,
                'is_active' => true,
            ]);

            $mapping->insertNode($organization);

            $idsByName[$name] = $organization->organization_id;
        }
    }
}
