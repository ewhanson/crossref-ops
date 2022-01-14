<?php

/**
 * @file plugins/generic/crossref/CrossrefInfoSender.php
 *
 * Copyright (c) 2013-2021 Simon Fraser University
 * Copyright (c) 2003-2021 John Willinsky
 * Distributed under The MIT License. For full terms see the file LICENSE.
 *
 * @class CrossrefInfoSender
 * @ingroup plugins_generic_crossref
 *
 * @brief Scheduled task to send deposits to Crossref and update statuses.
 */

import('lib.pkp.classes.scheduledTask.ScheduledTask');

use APP\facades\Repo;
use PKP\scheduledTask\ScheduledTask;

class CrossrefInfoSender extends ScheduledTask {
	/** @var $_plugin CrossRefExportPlugin */
	var $_plugin;

	/**
	 * Constructor.
	 * @param $argv array task arguments
	 */
	function __construct($args) {
		PluginRegistry::loadCategory('importexport');
		$plugin = PluginRegistry::getPlugin('importexport', 'CrossRefExportPlugin'); /* @var $plugin CrossRefExportPlugin */
		$this->_plugin = $plugin;

		if (is_a($plugin, 'CrossRefExportPlugin')) {
			$plugin->addLocaleData();
		}

		parent::__construct($args);
	}

	/**
	 * @copydoc ScheduledTask::getName()
	 */
	function getName() {
		return __('plugins.importexport.crossref.senderTask.name');
	}

	/**
	 * @copydoc ScheduledTask::executeActions()
	 */
	function executeActions() {
        if (!$this->_plugin) {
            return false;
        }

        $contexts = $this->_getServers();

        foreach ($contexts as $context) {
            Repo::doi()->depositAll($context);
        }
        return true;
	}

	/**
	 * Get all servers that meet the requirements to have
	 * their preprints or issues DOIs sent to Crossref.
	 * @return array
	 */
	function _getServers()
    {
        $plugin = $this->_plugin;
        $contextDao = Application::getContextDAO();
        /* @var $contextDao ServerDAO */
        $serverFactory = $contextDao->getAll(true);

        $servers = array();
        while ($server = $serverFactory->next()) {
            $serverId = $server->getId();
            if (!$plugin->getSetting($serverId, 'username') || !$plugin->getSetting($serverId, 'password') || !$plugin->getSetting($serverId, 'automaticRegistration')) continue;

            $doiPrefix = null;
            $pubIdPlugins = PluginRegistry::loadCategory('pubIds', true, $serverId);
            if (isset($pubIdPlugins['doipubidplugin'])) {
                $doiPubIdPlugin = $pubIdPlugins['doipubidplugin'];
                if (!$doiPubIdPlugin->getSetting($serverId, 'enabled')) continue;
                $doiPrefix = $doiPubIdPlugin->getSetting($serverId, 'doiPrefix');
            }

            if ($doiPrefix) {
                $servers[] = $server;
            } else {
                $this->addExecutionLogEntry(__('plugins.importexport.common.senderTask.warning.noDOIprefix', array('path' => $server->getPath())), SCHEDULED_TASK_MESSAGE_TYPE_WARNING);
            }
        }
        return $servers;
    }
}

