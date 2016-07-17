<?php

/**
 * @copyright Copyright (c) 2014 Carsten Brandt
 */

namespace OpenBook\Markdown\Block;

/**
 * Adds the leanpub style table blocks
 */
trait LeanpubTableTrait {

    /**
     * identify a line as the beginning of a table block.
     */
    protected function identifyLeanpubTable($line, $lines, $current) {
        return strpos($line, '|') !== false && preg_match('~^\|.*\|$~', $line);
    }

    /**
     * Consume lines for a table
     */
    protected function consumeLeanpubTable($lines, $current) {
        // consume until newline

        $block = [
            'leanpubTable',
            'cols' => [],
            'rows' => [],
            'hasHeader' => false,
            'hasFooter' => false
        ];

        $multiLineRows = false;
        $newRow = true;

        if (preg_match('/^\|[-\|]+\|$/', $lines[$current]))
            $current++; // skip the first line if it looks like |---|---|---|

        for ($i = $current, $count = count($lines); $i < $count; $i++) {
            $line = $lines[$i];

            // Parse optional header  |:---|:---:|---:|
            if ($i == $current + 1 && preg_match('/^\|[-:\|]+\|$/', $line)) {
                $block['hasHeader'] = true;
                $cols = explode('|', trim($line, ' |'));
                foreach ($cols as $col) {
                    $col = trim($col);
                    if (empty($col)) {
                        $block['cols'][] = '';
                        continue;
                    }
                    $l = ($col[0] === ':');
                    $r = (substr($col, -1, 1) === ':');
                    if ($l && $r) {
                        $block['cols'][] = 'center';
                    } elseif ($l) {
                        $block['cols'][] = 'left';
                    } elseif ($r) {
                        $block['cols'][] = 'right';
                    } else {
                        $block['cols'][] = '';
                    }
                }

                continue;
            }

            // Check for end of table
            if (trim($line) === '' || $line[0] !== '|') {
                break;
            }

            if (preg_match('/^\|[-\|]+\|$/', $line)) {
                if ($multiLineRows == false) {
                    // Merge previous tbody rows into the single one
                    $row2 = array_fill(0, count($block['rows'][0]), '');
                    $j = $block['hasHeader'] ? 1 : 0;
                    for (; $j < count($block['rows']); $j++) {
                        for ($k = 0; $k < count($block['rows'][$j]); $k++) {
                            $row2[$k] .= ' ' . $block['rows'][$j][$k];
                        }
                    }
                    if ($block['hasHeader'])
                        $block['rows'] = [$block['rows'][0], $row2];
                    else
                        $block['rows'] = [$row2];
                }
                $multiLineRows = true;
                $newRow = true;
                continue; // skip this line if it looks like |---|---|---|
            }

            // Optional footer  |===|===|===|
            if (preg_match('/^\|[=\|]+\|$/', $line)) {
                $block['hasFooter'] = true;
                continue;
            }

            $line = trim($line);
            $cells = explode('|', $line);
            array_shift($cells);
            array_pop($cells);

            if (!$multiLineRows || $newRow)
                $block['rows'][] = $cells;
            else {
                for ($k = 0; $k < count($cells); $k++) {
                    $block['rows'][count($block['rows']) - 1][$k] .= ' ' . $cells[$k];
                }
            }

            $newRow = false;
        }

        $rows2 = [];
        foreach ($block['rows'] as $row) {
            $row2 = [];
            foreach ($row as $cell) {
                $row2[] = $this->parseInline($cell);
            }
            $rows2[] = $row2;
        }
        $block['rows'] = $rows2;

        return [$block, --$i];
    }

    /**
     * render a table block
     */
    protected function renderLeanpubTable($block) {
        $content = '';
        $i = 0;
        if ($block['hasHeader']) {
            $content .= "<thead>\n<tr>\n";
            foreach ($block['rows'][0] as $cell)
                $content .= "<th>" . $this->renderAbsy($cell) . "</th>\n";
            $content .= "</tr>\n</thead>\n";
            $i++;
        }

        $content .= "<tbody>\n";
        $rowCount = count($block['rows']);
        if ($block['hasHeader'])
            $rowCount--;
        if ($block['hasFooter'])
            $rowCount--;

        for (; $i < $rowCount; $i++) {
            $content .= "<tr>\n";
            foreach ($block['rows'][$i] as $cell) {
                $content .= "<td>" . $this->renderAbsy($cell) . "</td>\n";
            }
            $content .= "</tr>\n";
        }
        $content .= "</tbody>\n";

        if ($block['hasFooter']) {
            $content .= "<tfoot>\n<tr>\n";
            foreach ($block['rows'][$i] as $cell)
                $content .= "<th>" . $this->renderAbsy($cell) . "</th>\n";
            $content .= "</tr>\n</tfoot>\n";
            $i++;
        }

        $content = "<table>\n$content</table>\n";

        $wrapper = "<div class=\"table-wrapper\">\n";
        if (isset($block['title'])) {
            $wrapper .= "<div class=\"table-caption\">" . $block['title'] . "</div>";
        }
        $wrapper .= $content;
        $wrapper .= "</div>\n";

        return $wrapper;
    }

}
