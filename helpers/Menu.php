<?php
/**
 * Menu Items
 * All Project Menu
 * @category  Menu List
 */

class Menu{
	
	
			public static $navbarsideleft = array(
		array(
			'path' => 'home', 
			'label' => 'Dashboard', 
			'icon' => '<i class="material-icons ">dashboard</i>'
		),
		
		array(
			'path' => 'tabpelanggaran', 
			'label' => 'Pelanggaran', 
			'icon' => '<i class="material-icons ">warning</i>'
		),
		
		array(
			'path' => 'tabjenispelanggaran', 
			'label' => 'Jenis Pelanggaran', 
			'icon' => '<i class="material-icons ">announcement</i>'
		),
		
		array(
			'path' => 'tabsiswa', 
			'label' => 'Siswa', 
			'icon' => '<i class="material-icons ">school</i>'
		),
		
		array(
			'path' => 'tabkelas', 
			'label' => 'Kelas', 
			'icon' => '<i class="material-icons ">class</i>'
		),
		
		array(
			'path' => 'tabjurusan', 
			'label' => 'Jurusan', 
			'icon' => '<i class="material-icons ">assignment</i>'
		),
		
		array(
			'path' => 'tabortu', 
			'label' => 'Orang tua', 
			'icon' => '<i class="material-icons ">face</i>'
		),
		
		array(
			'path' => 'tabguru', 
			'label' => 'Guru', 
			'icon' => '<i class="material-icons ">people</i>'
		),
		
		array(
			'path' => 'tabuser', 
			'label' => 'User', 
			'icon' => '<i class="material-icons ">person</i>'
		),
		
		array(
			'path' => 'role_permissions/add', 
			'label' => 'Role Permissions', 
			'icon' => ''
		),
		
		array(
			'path' => 'roles/add', 
			'label' => 'Roles', 
			'icon' => ''
		),
		
		array(
			'path' => 'tabsekolah', 
			'label' => 'Tabsekolah', 
			'icon' => '<i class="material-icons ">account_balance</i>'
		)
	);
		
	
	
			public static $jenkel = array(
		array(
			"value" => "1", 
			"label" => "Laki - laki", 
		),
		array(
			"value" => "2", 
			"label" => "Perempuan", 
		),);
		
}