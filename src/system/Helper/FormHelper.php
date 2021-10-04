<?php declare(strict_types=1);
namespace system\Helper;

class FormHelper {
    
    /** @var string $html */
    private $html;

    public function __construct(string $action, string $method, array $options = null)
    {
        $attributes = $this->makeAttributes($options);
        $this->html = "<form action=\"${action}\" method=\"${method}\" ${attributes}>";
    }

    /**
     * Closes the form
     * 
     * @return FormHelper
     **/
    public function close(): FormHelper
    {
        $this->html .= '</form>';
        return $this;
    }

    /**
     * Adds hidden input field
     * 
     * @param string $name
     * @param string $value
     * @return FormHelper
     */
    public function hidden(string $name, string $value): FormHelper
    {
        $this->html .= "<input type=\"hidden\" name=\"${name}\" value=\"${value}\">";
        return $this;
    }

    /**
     * Adds a text input field
     * 
     * @param string $name
     * @param string $label
     * @param array|null $options
     * @return FormHelper
     */
    public function text(string $name, string $label, array $options = null): FormHelper
    {
        $this->input(
            'text',
            $name,
            $label,
            array_merge(
                ['name' => $name],
                ($options ?? [])
            )
        );
        return $this;
    }

    /**
     * Adds a password field
     * 
     * @param string $name
     * @param string $label
     * @param array|null $options
     * @return FormHelper
     */
    public function password(string $name, string $label, array $options = null): FormHelper
    {
        $this->input(
            'password',
            $name,
            $label,
            array_merge(
                ['name' => $name],
                ($options ?? [])
            )
        );
        return $this;
    }

    /**
     * Adds an email field
     * 
     * @param string $name
     * @param string $label
     * @param array|null $options
     * @return FormHelper
     */
    public function email(string $name, string $label, array $options = null): FormHelper
    {
        $this->input(
            'email',
            $name,
            $label,
            array_merge(
                ['name' => $name],
                ($options ?? [])
            )
        );
        return $this;
    }

    /**
     * Adds a button
     * 
     * @param string $label
     * @param string $type
     * @param array|null $options
     * @return FormHelper
     */
    public function button(string $label, string $type, array $options = null): FormHelper
    {
        $attributes = $this->makeAttributes($options);
        $this->html .= "<button type=\"${type}\" $attributes>${label}</button>";
        return $this;
    }

    /**
     * Adds a checkbox
     * 
     * @param string $name
     * @param string $label
     * @param string $value
     * @param array|null $options
     * 
     * @return FormHelper
     */
    public function checkbox(string $name, string $label, string $value, array $options = null): FormHelper
    {
        $options['value'] = $value;
        $this->input(
            'checkbox',
            $name,
            $label,
            $options
        );
        return $this;
    }

    /**
     * Adds a radio group
     * 
     * @param string $name
     * @param string $label
     * @param array $radios
     * @return FormHelper
     */
    public function radioGroup(string $name, string $label, array $radios): FormHelper
    {
        $this->html .= '<div>';
        $this->html .= "<p>${label}</p>";
        foreach ($radios as $label => $value) {
            $this->html .= "<input type=\"radio\" id=\"${name}-${value}\" name=\"${name}\" value=\"${value}\">";
            $this->html .= "<label for=\"${name}-${value}\">${label}</label><br>";
        }
        $this->html .= '</div>';
        return $this;
    }

    /**
     * Adds a date field
     * 
     * @param string $name
     * @param string $label
     * @param array|null $options
     * @return FormHelper
     */
    public function date(string $name, string $label, array $options = null): FormHelper
    {
        $this->input(
            'date',
            $name,
            $label,
            array_merge(
                ['name' => $name],
                ($options ?? [])
            )
        );
        return $this;
    }

    /**
     * Adds a simple tag
     * 
     * Currently only p, bp, i, strong, em, small
     * 
     * @param string $type
     * @param string $content
     * @return FormHelper
     */
    public function tag(string $type, string $content): FormHelper
    {
        $allowedTags = [
            'p', 'b', 'i', 'strong', 'em', 'small'
        ];
        $this->html .= "<{$type}>{$content}</{$type}>";

        return $this;
    }

    /**
     * Starts a div element
     * 
     * @param array|null $options
     * @return FormHelper
     */
    public function divStart(array $options = null): FormHelper
    {
        $attributes = $this->makeAttributes($options);
        $this->html .= "<div ${attributes}>";

        return $this;
    }

    /**
     * Closes a div element
     * 
     * @return FormHelper
     */
    public function divEnd(): FormHelper
    {
        $this->html .= "</div>";

        return $this;
    }

    /** 
     * Inserts a break element
     * 
     * @return FormHelper
     */
    public function break(): FormHelper
    {
        $this->html .= "<br>";
        return $this;
    }

    /**
     * Creates an input field with the given type
     * 
     * @param string $type
     * @param string $name
     * @param string $label
     * @param array|null $options
     * @param bool $useLabel
     * @return void
     */
    private function input(string $type, string $name, string $label, array $options = null, bool $useLabel = true): void
    {
        $attributes = $this->makeAttributes($options);
        if ($useLabel)
        $this->html .= "<label for=\"${name}\">${label}</label>";

        $this->html .= "<input type=\"${type}\" id=\"${name}\" ${attributes}>";
    }

    /**
     * Translates options array in attribute string
     * 
     * @param array|null $options
     * @return string
     */
    private function makeAttributes(array $options = null) : string
    {
        $attributes = '';
        if ($options === null) return $attributes;

        foreach ($options as $attribute => $value) {
            $attributes .= "${attribute}=\"${value}\" ";
        }
        return $attributes;
    }

    /**
     * Retrieves the html for the form
     * 
     * @return string
     */
    public function getForm(): string
    {
        return $this->html;
    }

}
