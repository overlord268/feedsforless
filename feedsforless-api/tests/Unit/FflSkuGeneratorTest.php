<?php

namespace Tests\Unit;

use App\Domains\Catalog\Models\Category;
use App\Services\Catalog\FflSkuCodeDeriver;
use App\Services\Catalog\FflSkuGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FflSkuGeneratorTest extends TestCase
{
    private FflSkuGenerator $generator;

    private FflSkuCodeDeriver $deriver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator = new FflSkuGenerator;
        $this->deriver = new FflSkuCodeDeriver;
    }

    private function category(string $label, string $slug): Category
    {
        return new Category(['label' => $label, 'slug' => $slug]);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string, 3: string, 4: string, 5: string}>
     */
    public static function lockedSkuProvider(): array
    {
        return [
            'PHO DICAL 18.5%' => ['Phosphates', 'phosphates', 'Dicalcium Feed Phosphate', '18.5%', 'FFL-PHO-DICAL-185'],
            'PHO MOCAL 22.7%' => ['Phosphates', 'phosphates', 'Monocalcium Feed Phosphate', '22.7%', 'FFL-PHO-MOCAL-227'],
            'PHO MDCAL 21.0%' => ['Phosphates', 'phosphates', 'Monodicalcium Feed Phosphate', '21.0%', 'FFL-PHO-MDCAL-210'],
            'MAG MGOX 54F' => ['Magnesium Oxide', 'magnesium-oxide', 'Magnesium Oxide', '54% MgO (0.3–2.0 mm)', 'FFL-MAG-MGOX-54F'],
            'MAG MGOX 54C' => ['Magnesium Oxide', 'magnesium-oxide', 'Magnesium Oxide', '54% MgO (0.8–3.0 mm)', 'FFL-MAG-MGOX-54C'],
            'MAG MGOX HR95' => ['Magnesium Oxide', 'magnesium-oxide', 'Magnesium Oxide', 'HR 95 Block Grade', 'FFL-MAG-MGOX-HR95'],
            'URE UREA FG' => ['Prilled Urea', 'prilled-urea', 'Urea', 'Feed Grade', 'FFL-URE-UREA-FG'],
            'NAB NAHCO3 FG' => ['Na Buffers', 'na-buffers', 'Sodium Bicarbonate', 'Feed Grade', 'FFL-NAB-NAHCO3-FG'],
            'NAB TRONA STD' => ['Na Buffers', 'na-buffers', 'TronaCarb', 'Standard Grade', 'FFL-NAB-TRONA-STD'],
            'NAB TRONA PLT' => ['Na Buffers', 'na-buffers', 'TronaCarb', 'Poultry Grade', 'FFL-NAB-TRONA-PLT'],
            'NAB TRONA LOF' => ['Na Buffers', 'na-buffers', 'TronaCarb', 'Lo Fluor', 'FFL-NAB-TRONA-LOF'],
            'NAB BPAC STD' => ['Na Buffers', 'na-buffers', 'Buffer Pac', 'Standard', 'FFL-NAB-BPAC-STD'],
            'NAB BPAC 7030' => ['Na Buffers', 'na-buffers', 'Buffer Pac', '70/30 CB', 'FFL-NAB-BPAC-7030'],
        ];
    }

    #[DataProvider('lockedSkuProvider')]
    public function test_generates_locked_skus_from_excel(
        string $label,
        string $slug,
        string $productName,
        string $gradeSpec,
        string $expectedSku
    ): void {
        $this->assertSame(
            $expectedSku,
            $this->generator->generate($this->category($label, $slug), $productName, $gradeSpec)
        );
    }

    public function test_parse_grade_spec_returns_pending_when_empty(): void
    {
        $this->assertSame('PENDING', $this->generator->parseGradeSpec(null));
        $this->assertSame('PENDING', $this->generator->parseGradeSpec(''));
    }

    public function test_parse_grade_spec_normalizes_unicode_dashes_for_fine_coarse(): void
    {
        $this->assertSame('54F', $this->generator->parseGradeSpec('54% MgO (0.3-2.0 mm)'));
        $this->assertSame('54C', $this->generator->parseGradeSpec('54% MgO (0.8-3.0 mm)'));
    }

    public function test_parse_grade_spec_handles_integer_and_trailing_zero_percentages(): void
    {
        $this->assertSame('210', $this->generator->parseGradeSpec('21%'));
        $this->assertSame('185', $this->generator->parseGradeSpec('18.50%'));
    }

    public function test_derives_cat_and_prod_from_fields(): void
    {
        $this->assertSame('MAG', $this->deriver->catFromCategory($this->category('Magnesium Oxide', 'magnesium-oxide')));
        $this->assertSame('MGOX', $this->deriver->prodFromProductName('Magnesium Oxide 54% (0.3-2.0mm)'));
    }
}
