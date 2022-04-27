<?php
/**
 * Created by Andrey Stepanenko.
 * User: webnitros
 * Date: 24.04.2022
 * Time: 14:11
 */

namespace FileBrowserClient\Methods\Helpers;


use App\Config\Abstracts\DirectiveData;
use App\Config\Implement\Directive;

/**
 * @property array $dirs
 */
class Rules
{
    public function reset()
    {
        $this->rules = [];
    }

    public function addRule($regex = true, $allow = false, $path)
    {
        $this->rules[] = [
            "regex" => $regex,
            "allow" => $allow,
            "path" => $path,
            "regexp" => !$regex ? null : [
                "raw" => $path
            ],
        ];
    }

    public function getRules()
    {
        return $this->rules;
    }
}
