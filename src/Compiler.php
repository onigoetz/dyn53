<?php

/*
 * This class is inspired from Composer's compiler
 * @see https://github.com/composer/composer/blob/master/src/Composer/Compiler.php
 */

namespace Onigoetz\Dyn53;

use Symfony\Component\Finder\Finder;

/**
 * The Compiler class compiles dyn53 into a phar
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Jordi Boggiano <j.boggiano@seld.be>
 * @author Stéphane Goetz <stephane.goetz@onigoetz.ch>
 */
class Compiler
{
    /**
     * Compiles composer into a single phar file
     *
     * @throws \RuntimeException
     * @param  string $pharFile The full path to the file to create
     */
    public function compile($pharFile = 'dyn53.phar')
    {
        if (file_exists($pharFile)) {
            unlink($pharFile);
        }

        $phar = new \Phar($pharFile, 0, 'dyn53.phar');
        $phar->setSignatureAlgorithm(\Phar::SHA1);

        $phar->startBuffering();

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            ->name('*.php')
            ->notName('Compiler.php')
            ->in(__DIR__);

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $finder = new Finder();
        $finder->files()
            ->ignoreVCS(true)
            //->name('*.php')
            ->exclude('Tests')
            ->in(__DIR__ . '/../vendor/symfony/console')
            ->in(__DIR__ . '/../vendor/symfony/event-dispatcher')
            ->in(__DIR__ . '/../vendor/aws/aws-sdk-php/src/Aws/Route53/')
            ->in(__DIR__ . '/../vendor/aws/aws-sdk-php/src/Aws/Common/')
            ->in(__DIR__ . '/../vendor/guzzle/guzzle/src/')
            ->notPath('Guzzle/Batch')
            ->notPath('Guzzle/Cache')
            ->notPath('Guzzle/Plugin/Async')
            ->notPath('Guzzle/Plugin/Cache')
            ->notPath('Guzzle/Plugin/Cookie');

        foreach ($finder as $file) {
            $this->addFile($phar, $file);
        }

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/autoload.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_namespaces.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_psr4.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_classmap.php'));
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/autoload_real.php'));
        if (file_exists(__DIR__ . '/../vendor/composer/include_paths.php')) {
            $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/include_paths.php'));
        }
        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../vendor/composer/ClassLoader.php'));
        $this->addDyn53Bin($phar);

        // Stubs
        $phar->setStub($this->getStub());

        $phar->stopBuffering();

        $this->addFile($phar, new \SplFileInfo(__DIR__ . '/../LICENSE'), false);

        unset($phar);
    }

    private function addFile($phar, $file, $strip = true)
    {
        $path = strtr(str_replace(dirname(__DIR__) . DIRECTORY_SEPARATOR, '', $file->getRealPath()), '\\', '/');

        $content = file_get_contents($file);
        if ($strip) {
            $content = $this->stripWhitespace($content);
        } elseif ('LICENSE' === basename($file)) {
            $content = "\n" . $content . "\n";
        }

        $phar->addFromString($path, $content);
    }

    private function addDyn53Bin($phar)
    {
        $content = file_get_contents(__DIR__ . '/../bin/dyn53');
        $content = preg_replace('{^#!/usr/bin/env php\s*}', '', $content);
        $phar->addFromString('bin/dyn53', $content);
    }

    /**
     * Removes whitespace from a PHP source string while preserving line numbers.
     *
     * @param  string $source A PHP string
     * @return string The PHP string with the whitespace removed
     */
    private function stripWhitespace($source)
    {
        if (!function_exists('token_get_all')) {
            return $source;
        }

        $output = '';
        foreach (token_get_all($source) as $token) {
            if (is_string($token)) {
                $output .= $token;
            } elseif (in_array($token[0], array(T_COMMENT, T_DOC_COMMENT))) {
                $output .= str_repeat("\n", substr_count($token[1], "\n"));
            } elseif (T_WHITESPACE === $token[0]) {
                // reduce wide spaces
                $whitespace = preg_replace('{[ \t]+}', ' ', $token[1]);
                // normalize newlines to \n
                $whitespace = preg_replace('{(?:\r\n|\r|\n)}', "\n", $whitespace);
                // trim leading spaces
                $whitespace = preg_replace('{\n +}', "\n", $whitespace);
                $output .= $whitespace;
            } else {
                $output .= $token[1];
            }
        }

        return $output;
    }

    private function getStub()
    {
        return <<<'EOF'
#!/usr/bin/env php
<?php
/*
 * This file is part of Dyn53.
 *
 * (c) Stéphane Goetz <onigoetz@onigoetz.ch>
 *
 * For the full copyright and license information, please view
 * the license that is located at the bottom of this file.
 */

Phar::mapPhar('dyn53.phar');

require 'phar://dyn53.phar/bin/dyn53';

__HALT_COMPILER();
EOF;
    }
}
