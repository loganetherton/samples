<?php
namespace ValuePad\Core\Appraiser\Entities;

use ValuePad\Core\Document\Entities\Document;

class EoEx extends Eo
{
	/**
	 * @var bool
	 */
	private $question1;
	public function getQuestion1() { return $this->question1; }
	public function setQuestion1($question) { $this->question1 = $question; }

	/**
	 * @var string
	 */
	private $question1Explanation;
	public function getQuestion1Explanation() { return $this->question1Explanation; }
	public function setQuestion1Explanation($explanation) { $this->question1Explanation = $explanation; }

	/**
	 * @var Document
	 */
	private $question1Document;
	public function getQuestion1Document() { return $this->question1Document; }

	/**
	 * @param Document $document
	 */
	public function setQuestion1Document(Document $document = null)
	{
		$this->handleUsageOfOneDocument($this->getQuestion1Document(), $document);

		$this->question1Document = $document;
	}

	/**
	 * @var bool
	 */
	private $question2;
	public function getQuestion2() { return $this->question2; }
	public function setQuestion2($question) { $this->question2 = $question; }

	/**
	 * @var string
	 */
	private $question2Explanation;
	public function getQuestion2Explanation() { return $this->question2Explanation; }
	public function setQuestion2Explanation($explanation) { $this->question2Explanation = $explanation; }

	/**
	 * @var bool
	 */
	private $question3;
	public function getQuestion3() { return $this->question3; }
	public function setQuestion3($question) { $this->question3 = $question; }

	/**
	 * @var string
	 */
	private $question3Explanation;
	public function getQuestion3Explanation() { return $this->question3Explanation; }
	public function setQuestion3Explanation($explanation) { $this->question3Explanation = $explanation; }

	/**
	 * @var bool
	 */
	private $question4;
	public function getQuestion4() { return $this->question4; }
	public function setQuestion4($question) { $this->question4 = $question; }

	/**
	 * @var string
	 */
	private $question4Explanation;
	public function getQuestion4Explanation() { return $this->question4Explanation; }
	public function setQuestion4Explanation($explanation) { $this->question4Explanation = $explanation; }

	/**
	 * @var bool
	 */
	private $question5;
	public function getQuestion5() { return $this->question5; }
	public function setQuestion5($question) { $this->question5 = $question; }

	/**
	 * @var string
	 */
	private $question5Explanation;
	public function getQuestion5Explanation() { return $this->question5Explanation; }
	public function setQuestion5Explanation($explanation) { $this->question5Explanation = $explanation; }

	/**
	 * @var bool
	 */
	private $question6;
	public function getQuestion6() { return $this->question6; }
	public function setQuestion6($question) { $this->question6 = $question; }

	/**
	 * @var string
	 */
	private $question6Explanation;
	public function getQuestion6Explanation() { return $this->question6Explanation; }
	public function setQuestion6Explanation($explanation) { $this->question6Explanation = $explanation; }

	/**
	 * @var bool
	 */
	private $question7;
	public function getQuestion7() { return $this->question7; }
	public function setQuestion7($question) { $this->question7 = $question; }

	/**
	 * @var string
	 */
	private $question7Explanation;
	public function getQuestion7Explanation() { return $this->question7Explanation; }
	public function setQuestion7Explanation($explanation) { $this->question7Explanation = $explanation; }
}
