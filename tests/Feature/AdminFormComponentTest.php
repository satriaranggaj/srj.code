<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

/**
 * Form-component rendering contract.
 *
 * The bug this file exists to prevent: ComponentAttributeBag::only() and except()
 * accept a SINGLE argument in Laravel 10. Passing several positional arguments keeps
 * only the first, silently discarding the rest. That dropped `value` and `checked`
 * from every admin checkbox, which made a checked box submit the HTML default "on"
 * (rejected by boolean validation) and left already-featured projects rendering as
 * unchecked, so saving one silently cleared its flag.
 *
 * These assertions read the rendered DOM rather than the Blade source, because the
 * failure mode is precisely that the source looks correct.
 */
class AdminFormComponentTest extends TestCase
{
    /**
     * Every attribute that belongs on the <input>, not the wrapping <label>.
     *
     * @return array<string, array{0: string}>
     */
    public static function controlAttributeProvider(): array
    {
        return [
            'name' => ['name'],
            'value' => ['value'],
            'id' => ['id'],
            'disabled' => ['disabled'],
            'required' => ['required'],
            'readonly' => ['readonly'],
        ];
    }

    /**
     * @dataProvider controlAttributeProvider
     */
    public function test_control_attributes_reach_the_input(string $attribute): void
    {
        // Every control attribute is passed, then the one under test is asserted.
        $html = Blade::render(
            '<x-admin.checkbox name="featured" value="1" id="f1" :checked="true" disabled required readonly label="L" />'
        );

        $input = $this->firstTag($html, 'input');

        $this->assertStringContainsString(
            $attribute,
            $input,
            "The [{$attribute}] attribute never reached the checkbox <input>"
        );
    }

    public function test_checkbox_renders_the_submitted_value(): void
    {
        $html = Blade::render('<x-admin.checkbox name="featured" value="1" label="L" />');

        $this->assertStringContainsString(
            'value="1"',
            $this->firstTag($html, 'input'),
            'Without value="1" a checked box submits the HTML default "on"'
        );
    }

    public function test_checkbox_renders_checked_when_truthy(): void
    {
        $html = Blade::render('<x-admin.checkbox name="featured" value="1" :checked="true" label="L" />');

        $this->assertStringContainsStringIgnoringCase(
            'checked',
            $this->firstTag($html, 'input'),
            'A truthy :checked must render the checked attribute'
        );
    }

    public function test_checkbox_omits_checked_when_falsy(): void
    {
        $html = Blade::render('<x-admin.checkbox name="featured" value="1" :checked="false" label="L" />');

        $this->assertStringNotContainsStringIgnoringCase(
            'checked',
            $this->firstTag($html, 'input')
        );
    }

    /**
     * Control attributes on a <label> are invalid HTML and make the label itself
     * look like a form control to anything parsing the DOM.
     *
     * @dataProvider controlAttributeProvider
     */
    public function test_control_attributes_do_not_leak_onto_the_label(string $attribute): void
    {
        $html = Blade::render(
            '<x-admin.checkbox name="featured" value="1" id="f1" :checked="true" disabled required readonly label="L" />'
        );

        $label = $this->firstTag($html, 'label');

        $this->assertStringNotContainsString(
            $attribute,
            $label,
            "[{$attribute}] leaked onto the <label>; it belongs on the <input> only"
        );
    }

    public function test_presentation_attributes_still_reach_the_label(): void
    {
        $html = Blade::render('<x-admin.checkbox name="featured" class="mt-3" label="L" />');

        $this->assertStringContainsString('mt-3', $this->firstTag($html, 'label'));
    }

    public function test_checkbox_renders_its_label_and_description(): void
    {
        $html = Blade::render(
            '<x-admin.checkbox name="featured" value="1" label="Show in Selected work" description="Only for visible projects." />'
        );

        $this->assertStringContainsString('Show in Selected work', $html);
        $this->assertStringContainsString('Only for visible projects.', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | textarea
    |--------------------------------------------------------------------------
    */

    public function test_textarea_renders_its_slot(): void
    {
        $html = Blade::render('<x-admin.textarea name="description">Case study text.</x-admin.textarea>');

        $this->assertStringContainsString(
            'Case study text.',
            $this->firstTag($html, 'textarea', true),
            'x-admin.textarea dropped its content, so every textarea lost its value on re-render'
        );
    }

    public function test_textarea_renders_an_empty_slot_cleanly(): void
    {
        $html = Blade::render('<x-admin.textarea name="description"></x-admin.textarea>');

        $this->assertStringContainsString('<textarea', $html);
        $this->assertStringContainsString('name="description"', $html);
    }

    public function test_textarea_forwards_rows_and_attributes(): void
    {
        $html = Blade::render('<x-admin.textarea name="description" rows="6" maxlength="20000">x</x-admin.textarea>');

        $this->assertStringContainsString('rows="6"', $html);
        $this->assertStringContainsString('maxlength="20000"', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | input / select
    |--------------------------------------------------------------------------
    */

    public function test_input_renders_value_type_and_extra_attributes(): void
    {
        $html = Blade::render(
            '<x-admin.input id="title" name="title" type="text" maxlength="150" value="Lensku" />'
        );

        foreach (['id="title"', 'name="title"', 'type="text"', 'maxlength="150"', 'value="Lensku"'] as $needle) {
            $this->assertStringContainsString($needle, $html);
        }
    }

    public function test_input_accepts_a_null_value_without_crashing(): void
    {
        $html = Blade::render('<x-admin.input id="title" name="title" :value="null" />');

        $this->assertStringContainsString('name="title"', $html);
        $this->assertStringNotContainsString('value="null"', $html);
    }

    public function test_select_renders_the_selected_option(): void
    {
        $html = Blade::render(
            '<x-admin.select name="status" :options="[\'live\' => \'Live\', \'archived\' => \'Archived\']" selected="archived" />'
        );

        $this->assertMatchesRegularExpression(
            '/<option value="archived"[^>]*selected/',
            $html,
            'The selected option must carry the selected attribute'
        );
    }

    public function test_select_renders_a_placeholder(): void
    {
        $html = Blade::render(
            '<x-admin.select name="project_type" :options="[]" placeholder="— None —" />'
        );

        $this->assertStringContainsString('— None —', $html);
    }

    public function test_select_compares_selected_and_option_values_as_strings(): void
    {
        // Integer-ish config keys must still match a string selection.
        $html = Blade::render(
            '<x-admin.select name="n" :options="[1 => \'One\', 2 => \'Two\']" selected="2" />'
        );

        $this->assertMatchesRegularExpression('/<option value="2"[^>]*selected/', $html);
    }

    /**
     * The <input>/<textarea> opening tag, optionally with its content.
     */
    private function firstTag(string $html, string $tag, bool $withContent = false): string
    {
        $pattern = $withContent
            ? '/<'.$tag.'\b[^>]*>.*?<\/'.$tag.'>/is'
            : '/<'.$tag.'\b[^>]*>/i';

        $this->assertSame(1, preg_match($pattern, $html, $m), "Expected exactly one <{$tag}> element");

        return $m[0];
    }
}
