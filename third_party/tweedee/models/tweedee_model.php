<?php if ( ! defined('EXT')) exit('Invalid file request.');

/**
 * Tweedee model.
 *
 * @author			Stephen Lewis <stephen@experienceinternet.co.uk>
 * @copyright		Experience Internet
 * @package			Tweedee
 * @version 		0.1.0
 */

class Tweedee_model extends CI_Model {
	
	/* --------------------------------------------------------------
	 * PRIVATE PROPERTIES
	 * ------------------------------------------------------------ */

	/**
	 * ExpressionEngine object reference.
	 *
	 * @access	private
	 * @var		object
	 */
	private $_ee;
	
	/**
	 * Package name.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_name;

	/**
	 * Package theme URL.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_theme_url;
	
	/**
	 * Package version.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_package_version;
	
	/**
	 * The site ID.
	 *
	 * @access	private
	 * @var		string
	 */
	private $_site_id;
	
	
	
	/* --------------------------------------------------------------
	 * PUBLIC METHODS
	 * ------------------------------------------------------------ */

	/**
	 * Constructor.
	 *
	 * @access	public
	 * @param 	string		$package_name		The package name. Used during testing.
	 * @param	string		$package_version	The package version. Used during testing.
	 * @return	void
	 */
	public function __construct($package_name = '', $package_version = '')
	{
		parent::__construct();

		$this->_ee 				=& get_instance();
		$this->_package_name	= $package_name ? $package_name : 'tweedee';
		$this->_package_title	= 'Tweedee';
		$this->_package_version	= $package_version ? $package_version : '0.1.0';
	}
	
	
	/**
	 * Returns the package name.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_package_name()
	{
		return $this->_package_name;
	}
	
	
	/**
	 * Returns the URL of the themes folder.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_package_theme_url()
	{
		if ( ! $this->_package_theme_url)
		{
			$theme_url = $this->_ee->config->item('theme_folder_url');
			$theme_url = substr($theme_url, -1) == '/'
				? $theme_url .'third_party/'
				: $theme_url .'/third_party/';
			
			$this->_package_theme_url = $theme_url .strtolower($this->get_package_name()) .'/';
		}
		
		return $this->_package_theme_url;
	}
	
	
	/**
	 * Returns the package version.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_package_version()
	{
		return $this->_package_version;
	}
	
	
	/**
	 * Returns the site ID.
	 *
	 * @access	public
	 * @return	string
	 */
	public function get_site_id()
	{
		if ( ! $this->_site_id)
		{
			$this->_site_id = $this->_ee->config->item('site_id');
		}
		
		return $this->_site_id;
	}


	/**
	 * Installs the module.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function install_module()
	{
		$this->install_module_register();
		$this->install_module_search_criteria_table();
		
		return TRUE;
	}
	
	
	/**
	 * Register the module in the database.
	 *
	 * @access	public
	 * @return	void
	 */
	public function install_module_register()
	{
		$this->_ee->db->insert('modules', array(
			'has_cp_backend'		=> 'y',
			'has_publish_fields'	=> 'n',
			'module_name'			=> $this->get_package_name(),
			'module_version'		=> $this->get_package_version()
		));
	}


	/**
	 * Creates the module settings table.
	 *
	 * @access	public
	 * @return	void
	 */
	public function install_module_search_criteria_table()
	{
		$this->_ee->load->dbforge();

		// Table columns.
		$columns = array(
			'criterion_id' => array(
				'auto_increment'	=> TRUE,
				'constraint'		=> 10,
				'type'				=> 'INT',
				'unsigned'			=> TRUE
			),
			'site_id' => array(
				'constraint'		=> 5,
				'type'				=> 'INT',
				'unsigned'			=> TRUE
			),
			'criterion_type' => array(
				'constraint'		=> 32,
				'type'				=> 'VARCHAR'
			),
			'criterion_value' => array(
				'constraint'		=> 255,
				'type'				=> 'VARCHAR'
			)
		);

		$this->_ee->dbforge->add_field($columns);
		$this->_ee->dbforge->add_key('criterion_id', TRUE);
		$this->_ee->dbforge->create_table('tweedee_search_criteria', TRUE);
	}
	
	
	/**
	 * Uninstalls the module.
	 *
	 * @access	public
	 * @return	bool
	 */
	public function uninstall_module()
	{
		$this->_ee->load->dbforge();

		// Retrieve the module information.
		$db_module = $this->_ee->db
			->select('module_id')
			->get_where('modules', array('module_name' => $this->get_package_name()), 1);
		
		if ($db_module->num_rows() !== 1)
		{
			return FALSE;
		}
		
		// Delete module from the module_member_groups table.
		$this->_ee->db->delete('module_member_groups', array('module_id' => $db_module->row()->module_id));
		
		// Delete the module from the modules table.
		$this->_ee->db->delete('modules', array('module_name' => $this->get_package_name()));

		// Drop the 'search criteria' table.
		$this->_ee->dbforge->drop_table('tweedee_search_criteria');
		
		return TRUE;
	}
	
	
	/**
	 * Updates the module.
	 *
	 * @access	public
	 * @param 	string		$installed_version		The installed version.
	 * @param 	string		$package_version		The package version.
	 * @return	bool
	 */
	public function update_module($installed_version = '', $package_version = '')
	{
		if (version_compare($installed_version, $package_version, '>='))
		{
			return FALSE;
		}
		
		return TRUE;
	}
	
}

/* End of file		: tweedee_model.php */
/* File location	: third_party/tweedee/models/tweedee_model.php */
