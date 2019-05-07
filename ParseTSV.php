<?php

namespace App\Traits;

trait ParseTSV
{
    /**
     * @param string $tsv - value of a .tsv file (with column headers as first line)
     * @return array
     */
    public function tsvToArray($tsv)
    {
        $header = true;
        $data = [];
        $columns = null;
        $lines = explode(PHP_EOL, $tsv);

        if (count($lines) > 1 && trim($lines[1])) {
            foreach ($lines as $line) {
                if ($header) {
                    foreach (preg_split("/[\t]/", $line) as $item) {
                        $columns[] = $item;
                    }

                    $header = false;
                } else {
                    $row = [];

                    foreach (preg_split("/[\t]/", $line) as $key => $item) {
                        $row[$columns[$key]] = $item;
                    }

                    $data[] = $row;
                }
            }
        }

        return $data;
    }
}
