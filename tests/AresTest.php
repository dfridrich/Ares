<?php

use Defr\Ares;
use Defr\Ares\AresException;
use Defr\Ares\AresRecord;
use PHPUnit\Framework\TestCase;

final class AresTest extends TestCase
{
    /**
     * @var Ares
     */
    private $ares;

    protected function setUp(): void
    {
        $this->ares = new Ares(null, true);
		$this->ares->setApiClient(new Ares\ApiClient\AresV2()); // skip caching
    }

    /**
     * @param $companyId
     * @param $expectedException
     * @param $expectedExceptionMessage
     * @param $expected
     *
     * @dataProvider providerTestFindByIdentificationNumber
     *
     * @throws AresException
     */
    public function testFindByIdentificationNumber(
        $companyId,
        $expectedException,
        $expectedExceptionMessage,
        $expected
    ): void {
        // setup
        if ($expectedException !== null) {
            $this->expectException($expectedException);
        }
        if ($expectedExceptionMessage !== null) {
            $this->expectExceptionMessage($expectedExceptionMessage);
        }

        // when
        $actual = $this->ares->findByIdentificationNumber($companyId);

        // then
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function providerTestFindByIdentificationNumber(): array
    {
        return [
            [
                // integer ID number
                'companyId' => 48136450,
                'expectedException' => null,
                'expectedExceptionMessage' => null,
                'expected' => new AresRecord(
                    '48136450',
                    'CZ48136450',
                    'ČESKÁ NÁRODNÍ BANKA',
                    'Na příkopě',
                    '864',
                    '28',
                    'Praha 1',
                    'Nové Město',
                    '11000',
                    '19',
                    'N'
                ),
            ],
            [
                // string ID number
                'companyId' => '48136450',
                'expectedException' => null,
                'expectedExceptionMessage' => null,
                'expected' => new AresRecord(
                    '48136450',
                    'CZ48136450',
                    'ČESKÁ NÁRODNÍ BANKA',
                    'Na příkopě',
                    '864',
                    '28',
                    'Praha 1',
                    'Nové Město',
                    '11000',
                    '19',
                    'N'
                ),
            ],
            [
                // string ID number with leading zeros
                'companyId' => '00006947',
                'expectedException' => null,
                'expectedExceptionMessage' => null,
                'expected' => new AresRecord(
                    '00006947',
                    'CZ00006947',
                    'Ministerstvo financí',
                    'Letenská',
                    '525',
                    '15',
                    'Praha 1',
                    'Malá Strana',
                    '11800',
                    '19',
                    'N'
                ),
            ],
            [
                // company in insolvency
                'companyId' => '45275301',
                'expectedException' => null,
                'expectedExceptionMessage' => null,
                'expected' => new AresRecord(
                    '45275301',
                    'CZ45275301',
                    'MTH Praha a.s.',
                    'Kandertova',
                    '1131',
                    '1a',
                    'Praha 8',
                    'Libeň',
                    '18000',
                    '19',
                    'E'
                ),
            ],
            [
                // nonsense string ID number with some charaters in it
                'companyId' => 'ABC1234',
                'expectedException' => InvalidArgumentException::class,
                'expectedExceptionMessage' => 'IČ firmy musí být číslo.',
                'expected' => null,
            ],
            [
                // empty string ID number
                'companyId' => '',
                'expectedException' => InvalidArgumentException::class,
                'expectedExceptionMessage' => 'IČ firmy musí být číslo.',
                'expected' => null,
            ],
            [
                // non-existent ID number
                'companyId' => '12345678912345',
                'expectedException' => AresException::class,
                'expectedExceptionMessage' => 'IČ firmy nebylo nalezeno.',
                'expected' => null,
            ],
        ];
    }

    public function testFindByName(): void
    {
        $results = $this->ares->findByName('sever');

        $this->assertGreaterThan(0, count($results));
    }

    public function testFindByNameNonExistentName(): void
    {
        self::expectException(AresException::class);
        self::expectExceptionMessage('Nic nebylo nalezeno.');
        $this->ares->findByName('some non-existent company name');
    }

    public function testGetCompanyPeople()
    {
        $record = $this->ares->findByIdentificationNumber(27791394);
        $companyPeople = $record->getCompanyPeople();

        $this->assertCount(3, $companyPeople);
    }

    public function testBalancer(): void
    {
        $ares = new Ares();
		$ares->setApiClient(new Ares\ApiClient\AresV1()); // skip caching
        $ares->setBalancer('http://some.loadbalancer.domain');
        self::expectExceptionMessageMatches('/php_network_getaddresses/');
        try {
            $ares->findByIdentificationNumber(26168685);
        } catch (AresException $e) {
            throw $e;
        }
        $this->assertEquals(
            'http://some.loadbalancer.domain'
            .'?url=http%3A%2F%2Fwwwinfo.mfcr.cz%2Fcgi-bin%2Fares%2Fdarv_bas.cgi%3Fico%3D26168685',
            $ares->getLastUrl()
        );
    }

}
