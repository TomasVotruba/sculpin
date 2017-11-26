<?php declare(strict_types=1);

namespace Sculpin\Core\Tests\Source;

use Dflydev\Canal\Analyzer\Analyzer;
use Sculpin\Core\Source\FileSource;
use Sculpin\Core\Source\FilesystemDataSource;
use Sculpin\Core\Source\MemorySource;
use Symfony\Component\Finder\SplFileInfo;

class FileSourceTest extends \PHPUnit\Framework\TestCase
{
    /*
     * mock analyzer for detectFromFilename, should return text/html
     */

    public function makeTestSource(string $filename, bool $hasChanged = true)
    {
        $source = new FileSource(
            $this->makeTestAnalyzer(),
            $this->makeTestDatasource(),
            new SplFileInfo($filename, '../Fixtures', $filename),
            false,
            true
        );

        return $source;
    }

    public function makeTestAnalyzer()
    {
        $analyzer = $this->createMock('Dflydev\Canal\Analyzer\Analyzer');

        $analyzer
            ->expects($this->any())
            ->method('getInternetMediaTypeFactory')
            ->will($this->returnValue($this->makeTestInternetMediaFactory()));

        $analyzer
            ->expects($this->any())
            ->method('detectFromFilename')
            ->will($this->returnValue($this->makeTestInternetMediaType()));

        return $analyzer;
    }

    public function makeTestInternetMediaType()
    {
        $type = $this->createMock('Dflydev\Canal\InternetMediaType\InternetMediaTypeInterface');

        $type
            ->expects($this->any())
            ->method('getType')
            ->will($this->returnValue('text'));

        return $type;
    }

    public function makeTestInternetMediaFactory()
    {
        $factory = $this->createMock('Dflydev\Canal\InternetMediaType\InternetMediaTypeFactory');

        $factory
            ->expects($this->any())
            ->method('createApplicationXml')
            ->will($this->returnValue('html/yml'));

        return $factory;
    }

    public function makeTestDatasource()
    {
        $datasource = $this->createMock('Sculpin\Core\Source\DataSourceInterface');

        $datasource
            ->expects($this->any())
            ->method('dataSourceId')
            ->will($this->returnValue('FilesystemDataSource:test'));

        return $datasource;
    }

    /**
     * @dataProvider provideTestParseYaml
     */
    public function testParseYaml($filename, $msg)
    {
        $expectedOutput = $this->getErrorMessage($filename, $msg);
        ob_end_flush();
        ob_start();
        $this->makeTestSource($filename);
        $output = ob_get_contents();
        ob_clean();

        $this->assertEquals($expectedOutput, $output);
    }

    public function provideTestParseYaml()
    {
        return [
            [__DIR__ . '/../Fixtures/valid/no-end-frontmatter.yml', ''],
            [__DIR__ . '/../Fixtures/valid/frontmatter-nocontent.yml', ''],
            [__DIR__ . '/../Fixtures/valid/frontmatter-content.yml', ''],
            [
                __DIR__ . '/../Fixtures/invalid/one-line-edge-case.yml',
                'Yaml could not be parsed, parser detected a string.'
            ],
            [
                __DIR__ . '/../Fixtures/invalid/malformed-yaml.yml',
                'Yaml could not be parsed, parser detected a string.'
            ],
            [
                __DIR__ . '/../Fixtures/invalid/malformed-yaml2.yml',
                'Unable to parse at line 2 (near "first:fsdqf").'
            ],
        ];
    }

    public function getErrorMessage($filename, $msg)
    {
        if ($msg == '') {
            return '';
        }
        return ' ! FileSource:FilesystemDataSource:test:' . $filename . ' ' . $msg . ' !' . PHP_EOL;
    }
}
