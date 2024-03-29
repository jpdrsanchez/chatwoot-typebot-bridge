<?php

namespace Studio\Bridge\Support;

use stdClass;

class RichTextReader
{
    private function __construct()
    {
    }

    /**
     * @param stdClass[] $messages
     *
     * @return string
     */
    public static function parseToString(array $messages): string
    {
        $finalText = "";

        foreach ($messages as $message) {
            if ($message?->content?->richText) {
                $finalText .= self::formatRichText($message->content->richText);
            }

            if ($message?->content?->url) {
                $finalText .= "![Image]({$message->content->url})" . PHP_EOL;
            }
        }

        return $finalText;
    }

    /**
     * @param stdClass[] $blocks
     *
     * @return string
     */
    private static function formatRichText(array $blocks): string
    {
        $finalText = "";

        foreach ($blocks as $block) {
            $finalText .= array_reduce(
                $block->children,
                function ($carry, $item) {
                    if (! $item?->text) {
                        return $carry;
                    }

                    if (! empty($item->bold)) {
                        $carry .= "**$item->text**";
                    } elseif (! empty($item->italic)) {
                        $carry .= "*$item->text*";
                    } elseif (! empty($item->children)) {
                        $carry .= array_reduce(
                            $item->children,
                            function ($carry, $item) {
                                if (! $item?->text) {
                                    return $carry;
                                }

                                $carry .= "$item?->text";

                                return $carry;
                            },
                            "",
                        );
                    } else {
                        $carry .= "$item?->text";
                    }

                    return $carry;
                },
                ""
            ) . PHP_EOL;
        }

        return $finalText;
    }
}
