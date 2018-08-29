<?php
namespace ValuePad\DAL\Appraisal\Support;

use GuzzleHttp\Client;
use ValuePad\Core\Appraisal\Interfaces\ExtractorInterface;
use ValuePad\Core\Document\Entities\Document as Source;
use ValuePad\Core\Document\Persistables\DocumentPersistable as SourcePersistable;
use ValuePad\Support\Shortcut;
use ValuePad\Core\Document\Interfaces\DocumentPreferenceInterface as SourcePreferenceInterface;
use RuntimeException;
use ValuePad\Core\Document\Enums\Format as SourceFormat;
use Exception;
use SimpleXMLElement;

class Extractor implements ExtractorInterface
{
    const ENV_TO_PDF_URL = 'https://www.aiready.com/envtool/scripts_low/envtopdf.asp';

    private $client;

    /**
     * @var SourcePreferenceInterface
     */
    private $preference;

    /**
     * @param SourcePreferenceInterface $preference
     */
    public function __construct(SourcePreferenceInterface $preference)
    {
        $this->client = new Client();
        $this->preference = $preference;
    }

    /**
     * @param Source $source
     * @return SourcePersistable
     */
    public function fromXml(Source $source)
    {
        $url = Shortcut::extractUrlFromDocument($source, $this->preference);

        $xml = new SimpleXMLElement($url, LIBXML_PARSEHUGE, true);

        $resolvers = [
            function() use ($xml){
                $result = $this->node($xml, 'REPORT[0].EMBEDDED_FILE[0].DOCUMENT');
                return $result ? (string) $result : null;
            },
            function() use ($xml){
                foreach ($this->node($xml, 'REPORT[0].FORM', []) as $value) {

                    $node = $this->node($value, 'IMAGE[0].EMBEDDED_FILE[0]');

                    if (!$node || strtolower($node->attributes()['_Type'] ?? '') !== 'pdf'){
                        continue ;
                    }

                    if ($result = $this->node($node, 'DOCUMENT')){
                        return (string) $result;
                    }
                }

                return null;
            },
        ];

        $pdf = null;

        foreach ($resolvers as $resolver){
            if ($pdf = $resolver()){
                break ;
            }
        }

        if (!$pdf){
            throw new RuntimeException('Unable to extract PDF from the provided XML source.');
        }

        $target = tmpfile();
        stream_filter_append($target, 'convert.base64-decode', STREAM_FILTER_WRITE);
        fwrite($target, $pdf);

        $persistable = new SourcePersistable();

        $persistable->setLocation($target);
        $persistable->setSuggestedName($this->getFileName($source).'.pdf');

        return $persistable;
    }

    /**
     * @param SimpleXMLElement $xml
     * @param string $path
     * @param mixed $default
     * @return SimpleXMLElement
     */
    private function node(SimpleXMLElement $xml, $path, $default = null)
    {
        $node = $xml;

        foreach (explode('.', $path) as $part){

            $name = explode('[', $part);

            if ($index = $name[1] ?? null){
                $index = (int) rtrim($index, ']');
            }

            $name = $name[0];

            $node = $node->{$name};

            if (count($node) === 0){
                return $default;
            }

            if ($index !== null){
                if ($node[$index] === null){
                    return $default;
                }

                $node = $node[$index];
            }
        }

        return $node;
    }

    /**
     * @param  Source $source
     * @return SourcePersistable[]
     * @throws  Exception
     */
    public function fromEnv(Source $source)
    {
        $result = [];

        $env = tmpfile();
        fwrite($env, file_get_contents(Shortcut::extractUrlFromDocument($source, $this->preference)));

        $pdf = tmpfile();
        fwrite($pdf, $this->extractPdfContent($env, $source->getName()));

        $pdfPersistable = new SourcePersistable();
        $pdfPersistable->setLocation($pdf);
        $pdfPersistable->setSuggestedName($this->getFileName($source).'.pdf');

        $result[SourceFormat::PDF] = $pdfPersistable;

        $xml = tmpfile();

        if ($xmlContent = $this->extractXmlContent($env, $pdf)){
            fwrite($xml, $xmlContent);

            $xmlPersistable = new SourcePersistable();
            $xmlPersistable->setLocation($xml);
            $xmlPersistable->setSuggestedName($this->getFileName($source).'.xml');

            $result[SourceFormat::XML] = $xmlPersistable;
        }

        return $result;
    }

    /**
     * @param Source $source
     */
    private function getFileName(Source $source)
    {
        return pathinfo($source->getName())['filename'];
    }

    /**
     * @param resource $env
     * @param string $name
     * @return string
     */
    private function extractPdfContent($env, $name)
    {
        rewind($env);

        $contents = tmpfile();

        stream_copy_to_stream($env, $contents);

        rewind($contents);

        $result = $this->client->post(self::ENV_TO_PDF_URL, [
            'multipart' => [
                [
                    'name' => 'envfile',
                    'contents' => $contents,
                    'filename' => $name
                ]
            ]
        ]);

        if ($result->getStatusCode() != 200){
            throw new RuntimeException('Unable to extract PDF from the provided ENV file.');
        }

        return $result->getBody()->getContents();
    }

    /**
     * @param resource $env
     * @param resource $pdf
     * @return string
     */
    private function extractXmlContent($env, $pdf)
    {
        $pattern = '/<VALUATION_RESPONSE(.*?)VALUATION_RESPONSE>/s';

        $matches = [];

        rewind($env);

        if (preg_match($pattern, stream_get_contents($env), $matches) !== 1){
            return null;
        }

        $embed = '<EMBEDDED_FILE _Name="AppraisalReport" _EncodingType="Base64" MIMEType="application/pdf" _Type="PDF"><DOCUMENT>'.base64_encode(stream_get_contents($pdf)).'</DOCUMENT></EMBEDDED_FILE></REPORT>';

        return str_replace('</REPORT>',$embed, $matches[0]);

    }
}
