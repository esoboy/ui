<?php

// Larastan's Testbench application does not discover the package under analysis.
// Register its real views so view-string checks validate the shipped templates.
view()->addNamespace('ui', dirname(__DIR__).'/resources/views');
