<?php

namespace App\Filament\Forms\Components;

use App\Support\Markdown\CalloutType;
use Filament\Actions\Action;
use Filament\Forms\Components\MarkdownEditor;
use Illuminate\Support\Js;

class MarkdownCalloutActions
{
    public static function apply(MarkdownEditor $editor): MarkdownEditor
    {
        return $editor
            ->hint('Insert callout:')
            ->hintActions(array_map(self::action(...), CalloutType::cases()));
    }

    private static function action(CalloutType $type): Action
    {
        $label = $type === CalloutType::Hero ? 'Hero' : $type->defaultTitle();

        return Action::make("insert_{$type->value}_callout")
            ->label($label)
            ->tooltip($type === CalloutType::Hero ? 'Insert an intro panel' : "Insert a {$type->value} callout")
            ->alpineClickHandler(fn (MarkdownEditor $component): string => self::script($component->getId(), $type));
    }

    private static function script(string $fieldId, CalloutType $type): string
    {
        $field       = Js::from($fieldId);
        $marker      = Js::from('[!' . strtoupper($type->value) . ']');
        $placeholder = Js::from($type === CalloutType::Hero ? '# Headline' : 'Callout text');

        return <<<JS
            (() => {
                const editor = document.getElementById({$field})?._editor
                if (! editor) return
                const cm = editor.codemirror
                const from = cm.getCursor('from')
                const selection = cm.getSelection()
                const lead = from.ch > 0 ? '\\n\\n' : (from.line > 0 && cm.getLine(from.line - 1).trim() !== '' ? '\\n' : '')
                const body = (selection || {$placeholder}).split('\\n').map((line) => '> ' + line).join('\\n')
                cm.replaceSelection(lead + '> ' + {$marker} + '\\n' + body + '\\n\\n')
                if (! selection) {
                    const line = from.line + (from.ch > 0 ? 3 : (lead ? 2 : 1))
                    const start = {$placeholder}.startsWith('# ') ? 4 : 2
                    cm.setSelection({ line, ch: start }, { line, ch: cm.getLine(line).length })
                }
                cm.focus()
            })()
            JS;
    }
}
