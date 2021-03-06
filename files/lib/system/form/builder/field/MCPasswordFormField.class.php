<?php
namespace wcf\system\form\builder\field;
use wcf\system\form\builder\data\processor\CustomFormDataProcessor;
use wcf\system\form\builder\field\validation\FormFieldValidationError;
use wcf\system\form\builder\IFormDocument;
use wcf\util\ArrayUtil;
use wcf\util\JSON;
use wcf\util\StringUtil;

/**
 * Implementation of a form field for single-line text values.
 * 
 * @author	Matthias Schmidt
 * @copyright	2001-2019 WoltLab GmbH
 * @license	GNU Lesser General Public License <http://opensource.org/licenses/lgpl-license.php>
 * @package	WoltLabSuite\Core\System\Form\Builder\Field
 * @since	5.2
 */
class MCPasswordFormField extends AbstractFormField implements IAutoFocusFormField, IImmutableFormField, IMaximumLengthFormField, IMinimumLengthFormField, IPlaceholderFormField {
	use TAutoFocusFormField;
	use TImmutableFormField;
	use TMaximumLengthFormField;
	use TMinimumLengthFormField;
	use TPlaceholderFormField;
	
	/**
	 * @inheritDoc
	 */
	protected $javaScriptDataHandlerModule = 'WoltLabSuite/Core/Form/Builder/Field/Value';
	
	/**
	 * @inheritDoc
	 */
	protected $templateName = '__mcPasswordFormField';
	
	/**
	 * @var integer
	 */
	protected $minimumPasswordStrength;
	
	/**
	 * @var mixed[]
	 */
	protected $passwordStrength;
	
	/**
	 * @inheritDoc
	 */
	public function validate() {
		if ($this->isRequired() && ($this->getValue() === null || $this->getValue() === '')) {
			$this->addValidationError(new FormFieldValidationError('empty'));
		}
		else {
			$this->validateText($this->getValue());
		}
		
		parent::validate();
	}
	
	/**
	 * Checks the length of the given password.
	 * 
	 * @param	string		$text		validated password
	 */
	protected function validateText($text) {
		$this->validateMinimumLength($text);
		$this->validateMaximumLength($text);
		$this->validateMinimumStrength($text);
	}
	
	/**
	 * @inheritDoc
	 */
	public function readValue() {
		if ($this->getDocument()->hasRequestData($this->getPrefixedId())) {
			$value = $this->getDocument()->getRequestData($this->getPrefixedId());
			
			if (is_string($value)) {
				$this->value = StringUtil::trim($value);
			}
		}
		if ($this->getDocument()->hasRequestData($this->getPrefixedId() . '_passwordStrengthVerdict')) {
			$strength = $this->getDocument()->getRequestData($this->getPrefixedId() . '_passwordStrengthVerdict');
			
			if (is_string($strength)) {
				$this->passwordStrength = ArrayUtil::trim(JSON::decode($strength));
			}
			else if (is_array($strength)) {
				$this->passwordStrength = ArrayUtil::trim($strength);
			}
		}
		
		return $this;
	}
	
	/**
	 * Validates the minimum password strengh of the given password.
	 *
	 * @param	string		$text			validated password
	 * @param	string		$errorLanguageItem
	 */
	public function validateMinimumStrength($text, $errorLanguageItem = 'wcf.user.password.error.notSecure') {
		if ($this->getMinimumPasswordStrength() !== null && (empty($this->passwordStrength['score']) || $this->passwordStrength['score'] < $this->getMinimumPasswordStrength())) {
			$this->addValidationError(new FormFieldValidationError('notSecure', $errorLanguageItem));
		}
	}
	
	/**
	 * @param integer $minValue
	 */
	public function minimumPasswordStrength($minValue = PASSWORD_MIN_SCORE) {
		$this->minimumPasswordStrength = $minValue;
	}
	
	/**
	 * @return integer
	 */
	public function getMinimumPasswordStrength() : int {
		return $this->minimumPasswordStrength;
	}
	
	/**
	 * @inheritDoc
	 */
	public function populate() {
		parent::populate();
		
		$this->getDocument()->getDataHandler()->addProcessor(new CustomFormDataProcessor('passwordStrengthVerdict', function(IFormDocument $document, array $parameters) {
			if (isset($parameters['data'][$this->getObjectProperty() . '_passwordStrengthVerdict'])) {
				$parameters[$this->getObjectProperty() . '_passwordStrengthVerdict'] = is_string($parameters['data'][$this->getObjectProperty() . '_passwordStrengthVerdict']) ? JSON::decode($parameters['data'][$this->getObjectProperty() . '_passwordStrengthVerdict']) : $parameters['data'][$this->getObjectProperty() . '_passwordStrengthVerdict'];
			}
			
			return $parameters;
		}));
	}
}
