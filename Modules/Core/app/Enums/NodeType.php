<?php

namespace Modules\Core\Enums;

enum NodeType: string
{
    case Domain = 'domain';
    case Subdomain = 'subdomain';
    case Ip = 'ip';
    case Endpoint = 'endpoint';
    case JsFile = 'js_file';
    case Technology = 'technology';
}
