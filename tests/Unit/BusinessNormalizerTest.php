<?php

namespace Tests\Unit;

use App\Services\BusinessDataNormalizer;
use PHPUnit\Framework\TestCase;

class BusinessNormalizerTest extends TestCase
{
    protected BusinessDataNormalizer $normalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->normalizer = new BusinessDataNormalizer();
    }

    public function test_normalizes_whitespace_in_names_and_addresses(): void
    {
        $normalized = $this->normalizer->normalizeName('   ABC   Restaurant   ');
        $this->assertEquals('ABC Restaurant', $normalized);
    }

    public function test_normalizes_phone_numbers(): void
    {
        $phone1 = $this->normalizer->normalizePhone('  +91 98765-43210  ');
        $this->assertEquals('+919876543210', $phone1);

        $phone2 = $this->normalizer->normalizePhone('(079) 2654-1234');
        $this->assertEquals('07926541234', $phone2);
    }

    public function test_normalizes_urls(): void
    {
        $url1 = $this->normalizer->normalizeUrl('example.com/page');
        $this->assertEquals('https://example.com/page', $url1);

        $url2 = $this->normalizer->normalizeUrl('https://maps.google.com/?cid=12345');
        $this->assertEquals('https://maps.google.com/?cid=12345', $url2);
    }

    public function test_normalizes_emails(): void
    {
        $email = $this->normalizer->normalizeEmail('  INFO@Example.COM  ');
        $this->assertEquals('info@example.com', $email);

        $invalid = $this->normalizer->normalizeEmail('not-an-email');
        $this->assertNull($invalid);
    }

    public function test_normalizes_full_raw_payload(): void
    {
        $raw = [
            'name' => '  Dental   Care   ',
            'phone' => '+1 (555) 123-4567',
            'email' => 'contact@dental.com',
            'address' => "123 Main St,  \n Suite 400",
            'city' => 'Ahmedabad',
            'website' => 'dentalcare.com',
            'rating' => '4.5',
            'review_count' => '120',
            'place_id' => '  ChIJ12345  ',
        ];

        $res = $this->normalizer->normalize($raw);

        $this->assertEquals('Dental Care', $res['name']);
        $this->assertEquals('+15551234567', $res['phone']);
        $this->assertEquals('contact@dental.com', $res['email']);
        $this->assertEquals('123 Main St, Suite 400', $res['address']);
        $this->assertEquals('Ahmedabad', $res['city']);
        $this->assertEquals('https://dentalcare.com', $res['website']);
        $this->assertEquals(4.5, $res['rating']);
        $this->assertEquals(120, $res['review_count']);
        $this->assertEquals('ChIJ12345', $res['place_id']);
    }
}
