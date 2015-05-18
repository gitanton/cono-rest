<?php
function form_states_multi($name, $value = '', $include_blank = FALSE)
{
    $state_list = array('AL'=> "Alabama",
                        'AK'=> "Alaska",
                        'AZ'=> "Arizona",
                        'AR'=> "Arkansas",
                        'CA'=> "California",
                        'CO'=> "Colorado",
                        'CT'=> "Connecticut",
                        'DE'=> "Delaware",
                        'DC'=> "District Of Columbia",
                        'FL'=> "Florida",
                        'GA'=> "Georgia",
                        'HI'=> "Hawaii",
                        'ID'=> "Idaho",
                        'IL'=> "Illinois",
                        'IN'=> "Indiana",
                        'IA'=> "Iowa",
                        'KS'=> "Kansas",
                        'KY'=> "Kentucky",
                        'LA'=> "Louisiana",
                        'ME'=> "Maine",
                        'MD'=> "Maryland",
                        'MA'=> "Massachusetts",
                        'MI'=> "Michigan",
                        'MN'=> "Minnesota",
                        'MS'=> "Mississippi",
                        'MO'=> "Missouri",
                        'MT'=> "Montana",
                        'NE'=> "Nebraska",
                        'NV'=> "Nevada",
                        'NH'=> "New Hampshire",
                        'NJ'=> "New Jersey",
                        'NM'=> "New Mexico",
                        'NY'=> "New York",
                        'NC'=> "North Carolina",
                        'ND'=> "North Dakota",
                        'OH'=> "Ohio",
                        'OK'=> "Oklahoma",
                        'OR'=> "Oregon",
                        'PA'=> "Pennsylvania",
                        'RI'=> "Rhode Island",
                        'SC'=> "South Carolina",
                        'SD'=> "South Dakota",
                        'TN'=> "Tennessee",
                        'TX'=> "Texas",
                        'UT'=> "Utah",
                        'VT'=> "Vermont",
                        'VA'=> "Virginia",
                        'WA'=> "Washington",
                        'WV'=> "West Virginia",
                        'WI'=> "Wisconsin",
                        'WY'=> "Wyoming");

    echo form_multiselect($name . '[]', $state_list, $value, 'id="' . $name . '" size="6"');
}

function form_states($name, $value = '', $include_blank = FALSE, $attrs = '')
{
    $state_list = array('' => 'State',
                        'AL'=> "Alabama",
                        'AK'=> "Alaska",
                        'AZ'=> "Arizona",
                        'AR'=> "Arkansas",
                        'CA'=> "California",
                        'CO'=> "Colorado",
                        'CT'=> "Connecticut",
                        'DE'=> "Delaware",
                        'DC'=> "District Of Columbia",
                        'FL'=> "Florida",
                        'GA'=> "Georgia",
                        'HI'=> "Hawaii",
                        'ID'=> "Idaho",
                        'IL'=> "Illinois",
                        'IN'=> "Indiana",
                        'IA'=> "Iowa",
                        'KS'=> "Kansas",
                        'KY'=> "Kentucky",
                        'LA'=> "Louisiana",
                        'ME'=> "Maine",
                        'MD'=> "Maryland",
                        'MA'=> "Massachusetts",
                        'MI'=> "Michigan",
                        'MN'=> "Minnesota",
                        'MS'=> "Mississippi",
                        'MO'=> "Missouri",
                        'MT'=> "Montana",
                        'NE'=> "Nebraska",
                        'NV'=> "Nevada",
                        'NH'=> "New Hampshire",
                        'NJ'=> "New Jersey",
                        'NM'=> "New Mexico",
                        'NY'=> "New York",
                        'NC'=> "North Carolina",
                        'ND'=> "North Dakota",
                        'OH'=> "Ohio",
                        'OK'=> "Oklahoma",
                        'OR'=> "Oregon",
                        'PA'=> "Pennsylvania",
                        'RI'=> "Rhode Island",
                        'SC'=> "South Carolina",
                        'SD'=> "South Dakota",
                        'TN'=> "Tennessee",
                        'TX'=> "Texas",
                        'UT'=> "Utah",
                        'VT'=> "Vermont",
                        'VA'=> "Virginia",
                        'WA'=> "Washington",
                        'WV'=> "West Virginia",
                        'WI'=> "Wisconsin",
                        'WY'=> "Wyoming");

    //if (!$include_blank)
    //    array_unshift($state_list, '');

    echo form_dropdown($name, $state_list, $value, 'id="' . $name . '" ' . $attrs);
}

function form_countries($name, $value, $attrs='') {
    $country_list = array(
        'US'=>'United States',
        'AF'=>'Afghanistan',
        'AL'=>'Albania',
        'DZ'=>'Algeria',
        'AS'=>'American Samoa',
        'AD'=>'Andorra',
        'AO'=>'Angola',
        'AI'=>'Anguilla',
        'AQ'=>'Antarctica',
        'AG'=>'Antigua And Barbuda',
        'AR'=>'Argentina',
        'AM'=>'Armenia',
        'AW'=>'Aruba',
        'AU'=>'Australia',
        'AT'=>'Austria',
        'AZ'=>'Azerbaijan',
        'BS'=>'Bahamas',
        'BH'=>'Bahrain',
        'BD'=>'Bangladesh',
        'BB'=>'Barbados',
        'BY'=>'Belarus',
        'BE'=>'Belgium',
        'BZ'=>'Belize',
        'BJ'=>'Benin',
        'BM'=>'Bermuda',
        'BT'=>'Bhutan',
        'BO'=>'Bolivia',
        'BA'=>'Bosnia And Herzegovina',
        'BW'=>'Botswana',
        'BV'=>'Bouvet Island',
        'BR'=>'Brazil',
        'IO'=>'British Indian Ocean Territory',
        'BN'=>'Brunei',
        'BG'=>'Bulgaria',
        'BF'=>'Burkina Faso',
        'BI'=>'Burundi',
        'KH'=>'Cambodia',
        'CM'=>'Cameroon',
        'CA'=>'Canada',
        'CV'=>'Cape Verde',
        'KY'=>'Cayman Islands',
        'CF'=>'Central African Republic',
        'TD'=>'Chad',
        'CL'=>'Chile',
        'CN'=>'China',
        'CX'=>'Christmas Island',
        'CC'=>'Cocos (Keeling) Islands',
        'CO'=>'Columbia',
        'KM'=>'Comoros',
        'CG'=>'Congo',
        'CK'=>'Cook Islands',
        'CR'=>'Costa Rica',
        'CI'=>'Cote D\'Ivorie (Ivory Coast)',
        'HR'=>'Croatia (Hrvatska)',
        'CU'=>'Cuba',
        'CY'=>'Cyprus',
        'CZ'=>'Czech Republic',
        'CD'=>'Democratic Republic Of Congo (Zaire)',
        'DK'=>'Denmark',
        'DJ'=>'Djibouti',
        'DM'=>'Dominica',
        'DO'=>'Dominican Republic',
        'TP'=>'East Timor',
        'EC'=>'Ecuador',
        'EG'=>'Egypt',
        'SV'=>'El Salvador',
        'GQ'=>'Equatorial Guinea',
        'ER'=>'Eritrea',
        'EE'=>'Estonia',
        'ET'=>'Ethiopia',
        'FK'=>'Falkland Islands (Malvinas)',
        'FO'=>'Faroe Islands',
        'FJ'=>'Fiji',
        'FI'=>'Finland',
        'FR'=>'France',
        'FX'=>'France, Metropolitan',
        'GF'=>'French Guinea',
        'PF'=>'French Polynesia',
        'TF'=>'French Southern Territories',
        'GA'=>'Gabon',
        'GM'=>'Gambia',
        'GE'=>'Georgia',
        'DE'=>'Germany',
        'GH'=>'Ghana',
        'GI'=>'Gibraltar',
        'GR'=>'Greece',
        'GL'=>'Greenland',
        'GD'=>'Grenada',
        'GP'=>'Guadeloupe',
        'GU'=>'Guam',
        'GT'=>'Guatemala',
        'GN'=>'Guinea',
        'GW'=>'Guinea-Bissau',
        'GY'=>'Guyana',
        'HT'=>'Haiti',
        'HM'=>'Heard And McDonald Islands',
        'HN'=>'Honduras',
        'HK'=>'Hong Kong',
        'HU'=>'Hungary',
        'IS'=>'Iceland',
        'IN'=>'India',
        'ID'=>'Indonesia',
        'IR'=>'Iran',
        'IQ'=>'Iraq',
        'IE'=>'Ireland',
        'IL'=>'Israel',
        'IT'=>'Italy',
        'JM'=>'Jamaica',
        'JP'=>'Japan',
        'JO'=>'Jordan',
        'KZ'=>'Kazakhstan',
        'KE'=>'Kenya',
        'KI'=>'Kiribati',
        'KW'=>'Kuwait',
        'KG'=>'Kyrgyzstan',
        'LA'=>'Laos',
        'LV'=>'Latvia',
        'LB'=>'Lebanon',
        'LS'=>'Lesotho',
        'LR'=>'Liberia',
        'LY'=>'Libya',
        'LI'=>'Liechtenstein',
        'LT'=>'Lithuania',
        'LU'=>'Luxembourg',
        'MO'=>'Macau',
        'MK'=>'Macedonia',
        'MG'=>'Madagascar',
        'MW'=>'Malawi',
        'MY'=>'Malaysia',
        'MV'=>'Maldives',
        'ML'=>'Mali',
        'MT'=>'Malta',
        'MH'=>'Marshall Islands',
        'MQ'=>'Martinique',
        'MR'=>'Mauritania',
        'MU'=>'Mauritius',
        'YT'=>'Mayotte',
        'MX'=>'Mexico',
        'FM'=>'Micronesia',
        'MD'=>'Moldova',
        'MC'=>'Monaco',
        'MN'=>'Mongolia',
        'MS'=>'Montserrat',
        'MA'=>'Morocco',
        'MZ'=>'Mozambique',
        'MM'=>'Myanmar (Burma)',
        'NA'=>'Namibia',
        'NR'=>'Nauru',
        'NP'=>'Nepal',
        'NL'=>'Netherlands',
        'AN'=>'Netherlands Antilles',
        'NC'=>'New Caledonia',
        'NZ'=>'New Zealand',
        'NI'=>'Nicaragua',
        'NE'=>'Niger',
        'NG'=>'Nigeria',
        'NU'=>'Niue',
        'NF'=>'Norfolk Island',
        'KP'=>'North Korea',
        'MP'=>'Northern Mariana Islands',
        'NO'=>'Norway',
        'OM'=>'Oman',
        'PK'=>'Pakistan',
        'PW'=>'Palau',
        'PA'=>'Panama',
        'PG'=>'Papua New Guinea',
        'PY'=>'Paraguay',
        'PE'=>'Peru',
        'PH'=>'Philippines',
        'PN'=>'Pitcairn',
        'PL'=>'Poland',
        'PT'=>'Portugal',
        'PR'=>'Puerto Rico',
        'QA'=>'Qatar',
        'RE'=>'Reunion',
        'RO'=>'Romania',
        'RU'=>'Russia',
        'RW'=>'Rwanda',
        'SH'=>'Saint Helena',
        'KN'=>'Saint Kitts And Nevis',
        'LC'=>'Saint Lucia',
        'PM'=>'Saint Pierre And Miquelon',
        'VC'=>'Saint Vincent And The Grenadines',
        'SM'=>'San Marino',
        'ST'=>'Sao Tome And Principe',
        'SA'=>'Saudi Arabia',
        'SN'=>'Senegal',
        'SC'=>'Seychelles',
        'SL'=>'Sierra Leone',
        'SG'=>'Singapore',
        'SK'=>'Slovak Republic',
        'SI'=>'Slovenia',
        'SB'=>'Solomon Islands',
        'SO'=>'Somalia',
        'ZA'=>'South Africa',
        'GS'=>'South Georgia And South Sandwich Islands',
        'KR'=>'South Korea',
        'ES'=>'Spain',
        'LK'=>'Sri Lanka',
        'SD'=>'Sudan',
        'SR'=>'Suriname',
        'SJ'=>'Svalbard And Jan Mayen',
        'SZ'=>'Swaziland',
        'SE'=>'Sweden',
        'CH'=>'Switzerland',
        'SY'=>'Syria',
        'TW'=>'Taiwan',
        'TJ'=>'Tajikistan',
        'TZ'=>'Tanzania',
        'TH'=>'Thailand',
        'TG'=>'Togo',
        'TK'=>'Tokelau',
        'TO'=>'Tonga',
        'TT'=>'Trinidad And Tobago',
        'TN'=>'Tunisia',
        'TR'=>'Turkey',
        'TM'=>'Turkmenistan',
        'TC'=>'Turks And Caicos Islands',
        'TV'=>'Tuvalu',
        'UG'=>'Uganda',
        'UA'=>'Ukraine',
        'AE'=>'United Arab Emirates',
        'UK'=>'United Kingdom',
        'UM'=>'United States Minor Outlying Islands',
        'UY'=>'Uruguay',
        'UZ'=>'Uzbekistan',
        'VU'=>'Vanuatu',
        'VA'=>'Vatican City (Holy See)',
        'VE'=>'Venezuela',
        'VN'=>'Vietnam',
        'VG'=>'Virgin Islands (British)',
        'VI'=>'Virgin Islands (US)',
        'WF'=>'Wallis And Futuna Islands',
        'EH'=>'Western Sahara',
        'WS'=>'Western Samoa',
        'YE'=>'Yemen',
        'YU'=>'Yugoslavia',
        'ZM'=>'Zambia',
        'ZW'=>'Zimbabwe'
    );
    echo form_dropdown($name, $country_list, $value, 'id="' . $name . '" ' . $attrs);
}

function get_timezones() {
    $timezones = array(
        'Pacific/Midway'       => "(GMT-11:00) Midway Island",
        'US/Samoa'             => "(GMT-11:00) Samoa",
        'US/Hawaii'            => "(GMT-10:00) Hawaii",
        'US/Alaska'            => "(GMT-09:00) Alaska",
        'US/Pacific'           => "(GMT-08:00) Pacific Time (US &amp; Canada)",
        'America/Tijuana'      => "(GMT-08:00) Tijuana",
        'US/Arizona'           => "(GMT-07:00) Arizona",
        'US/Mountain'          => "(GMT-07:00) Mountain Time (US &amp; Canada)",
        'America/Chihuahua'    => "(GMT-07:00) Chihuahua",
        'America/Mazatlan'     => "(GMT-07:00) Mazatlan",
        'America/Mexico_City'  => "(GMT-06:00) Mexico City",
        'America/Monterrey'    => "(GMT-06:00) Monterrey",
        'Canada/Saskatchewan'  => "(GMT-06:00) Saskatchewan",
        'US/Central'           => "(GMT-06:00) Central Time (US &amp; Canada)",
        'US/Eastern'           => "(GMT-05:00) Eastern Time (US &amp; Canada)",
        'US/East-Indiana'      => "(GMT-05:00) Indiana (East)",
        'America/Bogota'       => "(GMT-05:00) Bogota",
        'America/Lima'         => "(GMT-05:00) Lima",
        'America/Caracas'      => "(GMT-04:30) Caracas",
        'Canada/Atlantic'      => "(GMT-04:00) Atlantic Time (Canada)",
        'America/La_Paz'       => "(GMT-04:00) La Paz",
        'America/Santiago'     => "(GMT-04:00) Santiago",
        'Canada/Newfoundland'  => "(GMT-03:30) Newfoundland",
        'America/Buenos_Aires' => "(GMT-03:00) Buenos Aires",
        'Greenland'            => "(GMT-03:00) Greenland",
        'Atlantic/Stanley'     => "(GMT-02:00) Stanley",
        'Atlantic/Azores'      => "(GMT-01:00) Azores",
        'Atlantic/Cape_Verde'  => "(GMT-01:00) Cape Verde Is.",
        'Africa/Casablanca'    => "(GMT) Casablanca",
        'Europe/Dublin'        => "(GMT) Dublin",
        'Europe/Lisbon'        => "(GMT) Lisbon",
        'Europe/London'        => "(GMT) London",
        'Africa/Monrovia'      => "(GMT) Monrovia",
        'Europe/Amsterdam'     => "(GMT+01:00) Amsterdam",
        'Europe/Belgrade'      => "(GMT+01:00) Belgrade",
        'Europe/Berlin'        => "(GMT+01:00) Berlin",
        'Europe/Bratislava'    => "(GMT+01:00) Bratislava",
        'Europe/Brussels'      => "(GMT+01:00) Brussels",
        'Europe/Budapest'      => "(GMT+01:00) Budapest",
        'Europe/Copenhagen'    => "(GMT+01:00) Copenhagen",
        'Europe/Ljubljana'     => "(GMT+01:00) Ljubljana",
        'Europe/Madrid'        => "(GMT+01:00) Madrid",
        'Europe/Paris'         => "(GMT+01:00) Paris",
        'Europe/Prague'        => "(GMT+01:00) Prague",
        'Europe/Rome'          => "(GMT+01:00) Rome",
        'Europe/Sarajevo'      => "(GMT+01:00) Sarajevo",
        'Europe/Skopje'        => "(GMT+01:00) Skopje",
        'Europe/Stockholm'     => "(GMT+01:00) Stockholm",
        'Europe/Vienna'        => "(GMT+01:00) Vienna",
        'Europe/Warsaw'        => "(GMT+01:00) Warsaw",
        'Europe/Zagreb'        => "(GMT+01:00) Zagreb",
        'Europe/Athens'        => "(GMT+02:00) Athens",
        'Europe/Bucharest'     => "(GMT+02:00) Bucharest",
        'Africa/Cairo'         => "(GMT+02:00) Cairo",
        'Africa/Harare'        => "(GMT+02:00) Harare",
        'Europe/Helsinki'      => "(GMT+02:00) Helsinki",
        'Europe/Istanbul'      => "(GMT+02:00) Istanbul",
        'Asia/Jerusalem'       => "(GMT+02:00) Jerusalem",
        'Europe/Kiev'          => "(GMT+02:00) Kyiv",
        'Europe/Minsk'         => "(GMT+02:00) Minsk",
        'Europe/Riga'          => "(GMT+02:00) Riga",
        'Europe/Sofia'         => "(GMT+02:00) Sofia",
        'Europe/Tallinn'       => "(GMT+02:00) Tallinn",
        'Europe/Vilnius'       => "(GMT+02:00) Vilnius",
        'Asia/Baghdad'         => "(GMT+03:00) Baghdad",
        'Asia/Kuwait'          => "(GMT+03:00) Kuwait",
        'Africa/Nairobi'       => "(GMT+03:00) Nairobi",
        'Asia/Riyadh'          => "(GMT+03:00) Riyadh",
        'Asia/Tehran'          => "(GMT+03:30) Tehran",
        'Europe/Moscow'        => "(GMT+04:00) Moscow",
        'Asia/Baku'            => "(GMT+04:00) Baku",
        'Europe/Volgograd'     => "(GMT+04:00) Volgograd",
        'Asia/Muscat'          => "(GMT+04:00) Muscat",
        'Asia/Tbilisi'         => "(GMT+04:00) Tbilisi",
        'Asia/Yerevan'         => "(GMT+04:00) Yerevan",
        'Asia/Kabul'           => "(GMT+04:30) Kabul",
        'Asia/Karachi'         => "(GMT+05:00) Karachi",
        'Asia/Tashkent'        => "(GMT+05:00) Tashkent",
        'Asia/Kolkata'         => "(GMT+05:30) Kolkata",
        'Asia/Kathmandu'       => "(GMT+05:45) Kathmandu",
        'Asia/Yekaterinburg'   => "(GMT+06:00) Ekaterinburg",
        'Asia/Almaty'          => "(GMT+06:00) Almaty",
        'Asia/Dhaka'           => "(GMT+06:00) Dhaka",
        'Asia/Novosibirsk'     => "(GMT+07:00) Novosibirsk",
        'Asia/Bangkok'         => "(GMT+07:00) Bangkok",
        'Asia/Jakarta'         => "(GMT+07:00) Jakarta",
        'Asia/Krasnoyarsk'     => "(GMT+08:00) Krasnoyarsk",
        'Asia/Chongqing'       => "(GMT+08:00) Chongqing",
        'Asia/Hong_Kong'       => "(GMT+08:00) Hong Kong",
        'Asia/Kuala_Lumpur'    => "(GMT+08:00) Kuala Lumpur",
        'Australia/Perth'      => "(GMT+08:00) Perth",
        'Asia/Singapore'       => "(GMT+08:00) Singapore",
        'Asia/Taipei'          => "(GMT+08:00) Taipei",
        'Asia/Ulaanbaatar'     => "(GMT+08:00) Ulaan Bataar",
        'Asia/Urumqi'          => "(GMT+08:00) Urumqi",
        'Asia/Irkutsk'         => "(GMT+09:00) Irkutsk",
        'Asia/Seoul'           => "(GMT+09:00) Seoul",
        'Asia/Tokyo'           => "(GMT+09:00) Tokyo",
        'Australia/Adelaide'   => "(GMT+09:30) Adelaide",
        'Australia/Darwin'     => "(GMT+09:30) Darwin",
        'Asia/Yakutsk'         => "(GMT+10:00) Yakutsk",
        'Australia/Brisbane'   => "(GMT+10:00) Brisbane",
        'Australia/Canberra'   => "(GMT+10:00) Canberra",
        'Pacific/Guam'         => "(GMT+10:00) Guam",
        'Australia/Hobart'     => "(GMT+10:00) Hobart",
        'Australia/Melbourne'  => "(GMT+10:00) Melbourne",
        'Pacific/Port_Moresby' => "(GMT+10:00) Port Moresby",
        'Australia/Sydney'     => "(GMT+10:00) Sydney",
        'Asia/Vladivostok'     => "(GMT+11:00) Vladivostok",
        'Asia/Magadan'         => "(GMT+12:00) Magadan",
        'Pacific/Auckland'     => "(GMT+12:00) Auckland",
        'Pacific/Fiji'         => "(GMT+12:00) Fiji",
    );
    return $timezones;
}

function form_timezone($name, $value='', $attrs = '') {
    echo form_dropdown($name, get_timezones(), $value, 'id="' . $name . '" ' . $attrs);
}

function form_table($table_name, $name, $value = '', $attrs = '', $order_col = 'id', $include_blank = FALSE)
{
    $CI =& get_instance();
    $CI->load->database();

    $options = array();
    $query = $CI->db->order_by($order_col, 'asc')->get($table_name);
    foreach ($query->result() as $row) {
        $options[$row->id] = ucwords($row->name);
    }

    if ($include_blank) {
        $options[''] = '';
    }

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_phone($name, $value)
{
    echo '<div class="combined phone">';
    echo '	<p class="small">';
    echo '		<input type="text" class="{validate: {maxlength:3,digits:true}}" maxlength="3" name="' . $name . '1" id="' . $name . '1" ';
    echo '		value="' . phone_part($value, 1) . '"></p>';
    echo '	<p class="small">';
    echo '		<input type="text" class="{validate: {maxlength:3,digits:true}}" maxlength="3" name="' . $name . '2" id="' . $name . '2" ';
    echo '		value="' . phone_part($value, 2) . '"></p>';
    echo '	<p class="small last">';
    echo '		<input type="text" class="{validate: {maxlength:4,digits:true}}" maxlength="4" name="' . $name . '3" id="' . $name . '3" ';
    echo '		value="' . phone_part($value, 3) . '"></p>';
    echo '</div>';
}

function form_title($name, $value = '', $attrs = '')
{
    $options = array(''=> '', 'Mr.'=> "Mr.", 'Mrs.'=> "Mrs.", 'Miss'=> "Miss", 'Ms.'=> "Ms.");
    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_yes_no($name, $value = '', $attrs = '')
{
    $options = array(1 => 'Yes', 0 => 'No');
    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_approve_reject($name, $value = '', $attrs = '')
{
    $options = array(REQUEST_STATUS_APPROVED => 'Approve', REQUEST_STATUS_DENIED => 'Deny');
    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_status($name, $value = '', $attrs = '')
{
    $options = array(1 => 'Enabled', 0 => 'Disabled');
    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_active($name, $value = '', $attrs = '')
{
    $options = array(1 => 'Inactive', 0 => 'Active');
    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_hour($name, $value = '', $attrs = '')
{
    $options = array("00:00:00"=> "12:00 AM");
    //$options = array("00:30:00"=> "12:30 AM");
    for ($i = 1; $i < 10; $i++) {
        $options["0" . $i . ":00:00"] = "0" . $i . ":00 AM";
        //$options["0" . $i . ":30:00"] = "0" . $i . ":30 AM";
    }
    $options["10:00:00"] = "10:00 AM";
    //$options["10:30:00"] = "10:30 AM";
    $options["11:00:00"] = "11:00 AM";
    //$options["11:30:00"] = "11:30 AM";


    $options["12:00:00"] = "12:00 PM";
    //$options["12:30:00"] = "12:30 PM";
    for ($i = 1; $i < 8; $i++) {
        $options["1" . ($i + 2) . ":00:00"] = "0" . $i . ":00 PM";
        //$options["1" . ($i + 2) . ":30:00"] = "0" . $i . ":30 PM";
    }
    $options["20:00:00"] = "08:00 PM";
    //$options["20:30:00"] = "08:30 PM";
    $options["21:00:00"] = "09:00 PM";
    //$options["21:30:00"] = "09:30 PM";
    $options["22:00:00"] = "10:00 PM";
    //$options["22:30:00"] = "10:30 PM";
    $options["23:00:00"] = "11:00 PM";
    //$options["23:30:00"] = "11:30 PM";

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_month($name, $value = '', $allow_blank = FALSE, $attrs = '')
{

    if (!$allow_blank && !$value) {
        $value = date("m");
    } else if (strlen($value) > 2) {
        $value = substr($value, 5, 2);
    }
    if ($allow_blank) {
        $options = array(""  => "", "01"=> "January", "02"=> "February", "03"=> "March", "04"=> "April", "05"=> "May", "06"=> "June",
                         "07"=> "July", "08"=> "August", "09"=> "September", "10"=> "October", "11"=> "November", "12"=> "December");
    } else {
        $options = array("01"=> "January", "02"=> "February", "03"=> "March", "04"=> "April", "05"=> "May", "06"=> "June",
                         "07"=> "July", "08"=> "August", "09"=> "September", "10"=> "October", "11"=> "November", "12"=> "December");
    }

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_day_of_week($name, $value = '', $attrs = '')
{

    $options = array("1"=> "Sunday", "2"=> "Monday", "3"=> "Tuesday", "4"=> "Wednesday", "5"=> "Thursday", "6"=> "Friday",
                     "7"=> "Saturday");

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_week_of_month($name, $value = '', $attrs = '')
{

    $options = array("1"=> "First", "2"=> "Second", "3"=> "Third", "4"=> "Fourth", "5"=> "Fifth");

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_day($name, $value = '', $allow_blank = FALSE)
{
    if (!$allow_blank && !$value) {
        $value = date("d");
    } else if (strlen($value) > 2) {
        $value = substr($value, 8, 2);
    }
    $options = array();
    for ($i = 1; $i < 32; $i++) {
        if ($i < 10)
            $label = '0' . $i;
        else
            $label = $i;

        $options[$i] = $label;
    }
    if ($allow_blank) {
        array_unshift($options, "");
    }

    echo form_dropdown($name, $options, $value, 'id="' . $name . '"');
}

function form_year($name, $value = '', $allow_blank = FALSE, $attrs = '')
{
    if (!$allow_blank && !$value) {
        $value = date("Y");
    } else if (strlen($value) > 2) {
        $value = substr($value, 0, 4);
    }
    $options = array();
    if ($allow_blank) {
        $options[""] = "";
    }
    for ($i = 5; $i > -5; $i--) {
        $year = date("Y") + $i;

        $options[(string)$year] = $year;
    }

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

function form_year_future($name, $value = '', $attrs = '')
{
    if (!$value) {
        $value = date("Y");
    } else if (strlen($value) > 2) {
        $value = substr($value, 0, 4);
    }
    $options = array();
    for ($i = 0; $i < 10; $i++) {
        $year = date("Y") + $i;

        $options[$year] = $year;
    }

    echo form_dropdown($name, $options, $value, 'id="' . $name . '" ' . $attrs);
}

?>