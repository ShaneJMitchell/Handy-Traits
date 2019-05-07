<?php

namespace App\Traits;

trait FileHandler
{
    /**
     * @param string $filename
     * @return bool|string
     */
    public function getFileContents($filename)
    {
        if (!$filename || !is_readable($filename)) {
            throw new \InvalidArgumentException("File doesn't exist or is not readable: " . $filename);
        }

        return file_get_contents($filename);
    }

    /**
     * Returns lines from the end of a file - Thanks fseek: http://php.net/manual/en/function.fseek.php#69008
     * @param string $file
     * @param int $lines
     * @return string|array
     */
    function getFileLines($file, $lines = 1)
    {
        try {
            $handle = fopen($file, "r");
        } catch (\Exception $e) {
            return ($lines === 1) ? '' : [];
        }

        $linecounter = $lines;
        $pos = -2;
        $beginning = false;
        $text = array();

        while ($linecounter > 0) {
            $t = " ";

            while ($t != "\n") {
                if (fseek($handle, $pos, SEEK_END) == -1) {
                    $beginning = true;
                    break;
                }

                $t = fgetc($handle);
                $pos--;
            }

            $linecounter--;

            if ($beginning) rewind($handle);

            $text[$lines - $linecounter - 1] = fgets($handle);

            if ($beginning) break;
        }

        fclose ($handle);

        return ($lines === 1) ? $text[0] : array_reverse($text);
    }
}
