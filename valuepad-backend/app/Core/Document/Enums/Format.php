<?php
namespace ValuePad\Core\Document\Enums;

use Ascope\Libraries\Enum\Enum;
use ValuePad\Core\Document\Persistables\DocumentPersistable;

class Format extends Enum
{
	const JPEG = 'jpeg';
	const JPG = 'jpg';
	const XLS = 'xls';
	const XLSX = 'xlsx';
	const GIF = 'gif';
	const PNG = 'png';
	const TXT = 'txt';
	const DOC = 'doc';
	const DOCX = 'docx';
	const CSV = 'csv';
	const PDF = 'pdf';
	const XML = 'xml';
	const ACI = 'aci';
	const ZAP = 'zap';
	const ENV = 'env';
	const ZOO = 'zoo';
	const HTML = 'html';
    const ODT = 'odt';
    const FILE = 'file';
    const EMZ = 'emz';
    const SIGIMG0 = 'sigimg0';
    const SIGIMG1 = 'sigimg1';
    const SIGIMG2 = 'sigimg2';
    const SIGIMG3 = 'sigimg3';
    const TIF = 'tif';
    const WMZ = 'wmz';
    const RTF = 'rtf';
    const PSD = 'psd';

	/**
	 * @param DocumentPersistable $persistable
	 * @return Format
	 */
	public static function toFormat(DocumentPersistable $persistable)
	{
		$ext = strtolower(pathinfo($persistable->getSuggestedName(), PATHINFO_EXTENSION));

		if (!static::has($ext)){
			return null;
		}

		return new static($ext);
	}
}
