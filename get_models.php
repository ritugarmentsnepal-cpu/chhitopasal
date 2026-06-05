<?php
$c = file_get_contents('https://openrouter.ai/api/v1/models');
file_put_contents('models.json', $c);
