<?php

/**
 * @defgroup plugins_importexport_crossref CrossRef Plugin
 */

/**
 * @file plugins/importexport/crossref/index.php
 *
 * Copyright (c) 2014-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under The MIT License. For full terms see the file LICENSE.
 *
 * @ingroup plugins_generic_crossref
 * @brief Wrapper for Crossref plugin.
 *
 */

require_once('CrossrefPlugin.inc.php');

return new CrossrefPlugin();


