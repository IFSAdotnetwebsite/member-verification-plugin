<?php 
define( 'IFSA_FORM_1_TITLE', 'Are you a Student?' );
define( 'IFSA_FORM_2_TITLE', 'Univerity information' );
define( 'IFSA_FORM_3_TITLE', 'Are you an IFSA Member?' );
define( 'IFSA_FORM_6_TITLE', 'Graduation day' );
define( 'IFSA_FORM_4_TITLE', 'When have you Graduate?' );
define( 'IFSA_FORM_7_TITLE', 'Univerity information' );

define("IFSA_NO_MEMBERSHIP_LEVEL", 0);
define("IFSA_LC_MEMBER_LEVEL", 1);
define("IFSA_LC_ADMIN_LEVEL", 2);
define("IFSA_FORMER_MEMBER_LEVEL", 3);
define("IFSA_MEMBER_REMOVED_LEVEL", 4);

/* The country list is from here https://stefangabos.github.io/world_countries/ which follows the IS0-3166 standard
it takes the dataset "world" which includes all countries and territories */
const COUNTRY_LIST = array(
    "Afghanistan",
    "Åland Islands",
    "Albania",
    "Algeria",
    "American Samoa",
    "Andorra",
    "Angola",
    "Anguilla",
    "Antarctica",
    "Antigua and Barbuda",
    "Argentina",
    "Armenia",
    "Aruba",
    "Australia",
    "Austria",
    "Azerbaijan",
    "Bahamas",
    "Bahrain",
    "Bangladesh",
    "Barbados",
    "Belarus",
    "Belgium",
    "Belize",
    "Benin",
    "Bermuda",
    "Bhutan",
    "Bolivia (Plurinational State of)",
    "Bonaire, Sint Eustatius and Saba",
    "Bosnia and Herzegovina",
    "Botswana",
    "Bouvet Island",
    "Brazil",
    "British Indian Ocean Territory",
    "Brunei Darussalam",
    "Bulgaria",
    "Burkina Faso",
    "Burundi",
    "Cabo Verde",
    "Cambodia",
    "Cameroon",
    "Canada",
    "Cayman Islands",
    "Central African Republic",
    "Chad",
    "Chile",
    "China",
    "Christmas Island",
    "Cocos (Keeling) Islands",
    "Colombia",
    "Comoros",
    "Congo",
    "Congo, Democratic Republic of the",
    "Cook Islands",
    "Costa Rica",
    "Côte d'Ivoire",
    "Croatia",
    "Cuba",
    "Curaçao",
    "Cyprus",
    "Czechia",
    "Denmark",
    "Djibouti",
    "Dominica",
    "Dominican Republic",
    "Ecuador",
    "Egypt",
    "El Salvador",
    "Equatorial Guinea",
    "Eritrea",
    "Estonia",
    "Eswatini",
    "Ethiopia",
    "Falkland Islands (Malvinas)",
    "Faroe Islands",
    "Fiji",
    "Finland",
    "France",
    "French Guiana",
    "French Polynesia",
    "French Southern Territories",
    "Gabon",
    "Gambia",
    "Georgia",
    "Germany",
    "Ghana",
    "Gibraltar",
    "Greece",
    "Greenland",
    "Grenada",
    "Guadeloupe",
    "Guam",
    "Guatemala",
    "Guernsey",
    "Guinea",
    "Guinea-Bissau",
    "Guyana",
    "Haiti",
    "Heard Island and McDonald Islands",
    "Holy See",
    "Honduras",
    "Hong Kong",
    "Hungary",
    "Iceland",
    "India",
    "Indonesia",
    "Iran (Islamic Republic of)",
    "Iraq",
    "Ireland",
    "Isle of Man",
    "Israel",
    "Italy",
    "Jamaica",
    "Japan",
    "Jersey",
    "Jordan",
    "Kazakhstan",
    "Kenya",
    "Kiribati",
    "Korea (Democratic People's Republic of)",
    "Korea, Republic of",
    "Kuwait",
    "Kyrgyzstan",
    "Lao People's Democratic Republic",
    "Latvia",
    "Lebanon",
    "Lesotho",
    "Liberia",
    "Libya",
    "Liechtenstein",
    "Lithuania",
    "Luxembourg",
    "Macao",
    "Madagascar",
    "Malawi",
    "Malaysia",
    "Maldives",
    "Mali",
    "Malta",
    "Marshall Islands",
    "Martinique",
    "Mauritania",
    "Mauritius",
    "Mayotte",
    "Mexico",
    "Micronesia (Federated States of)",
    "Moldova, Republic of",
    "Monaco",
    "Mongolia",
    "Montenegro",
    "Montserrat",
    "Morocco",
    "Mozambique",
    "Myanmar",
    "Namibia",
    "Nauru",
    "Nepal",
    "Netherlands",
    "New Caledonia",
    "New Zealand",
    "Nicaragua",
    "Niger",
    "Nigeria",
    "Niue",
    "Norfolk Island",
    "North Macedonia",
    "Northern Mariana Islands",
    "Norway",
    "Oman",
    "Pakistan",
    "Palau",
    "Palestine, State of",
    "Panama",
    "Papua New Guinea",
    "Paraguay",
    "Peru",
    "Philippines",
    "Pitcairn",
    "Poland",
    "Portugal",
    "Puerto Rico",
    "Qatar",
    "Réunion",
    "Romania",
    "Russian Federation",
    "Rwanda",
    "Saint Barthélemy",
    "Saint Helena, Ascension and Tristan da Cunha",
    "Saint Kitts and Nevis",
    "Saint Lucia",
    "Saint Martin (French part)",
    "Saint Pierre and Miquelon",
    "Saint Vincent and the Grenadines",
    "Samoa",
    "San Marino",
    "Sao Tome and Principe",
    "Saudi Arabia",
    "Senegal",
    "Serbia",
    "Seychelles",
    "Sierra Leone",
    "Singapore",
    "Sint Maarten (Dutch part)",
    "Slovakia",
    "Slovenia",
    "Solomon Islands",
    "Somalia",
    "South Africa",
    "South Georgia and the South Sandwich Islands",
    "South Sudan",
    "Spain",
    "Sri Lanka",
    "Sudan",
    "Suriname",
    "Svalbard and Jan Mayen",
    "Sweden",
    "Switzerland",
    "Syrian Arab Republic",
    "Taiwan, Province of China",
    "Tajikistan",
    "Tanzania, United Republic of",
    "Thailand",
    "Timor-Leste",
    "Togo",
    "Tokelau",
    "Tonga",
    "Trinidad and Tobago",
    "Tunisia",
    "Turkey",
    "Turkmenistan",
    "Turks and Caicos Islands",
    "Tuvalu",
    "Uganda",
    "Ukraine",
    "United Arab Emirates",
    "United Kingdom of Great Britain and Northern Ireland",
    "United States of America",
    "United States Minor Outlying Islands",
    "Uruguay",
    "Uzbekistan",
    "Vanuatu",
    "Venezuela (Bolivarian Republic of)",
    "Viet Nam",
    "Virgin Islands (British)",
    "Virgin Islands (U.S.)",
    "Wallis and Futuna",
    "Western Sahara",
    "Yemen",
    "Zambia",
    "Zimbabwe",

);
const GENDERS = array('Male', 'Female', 'Other non-binary', 'Prefer not to say');
const UNI_LEVELS = array('Bachelor Degree', 'Master Degree', 'PhD');

// list of email fields with their default values
const IFSA_EMAILS = array(
    'welcome_email_after_verify_member' => array(
        'description'=>'Welcome Email after verify member',
        'subject' => 'Successfully verified to IFSA',
        'to' => '{$user_email}'
    ),
    'reminder_on_same_date_when_renewed' =>array(
        'description'=>'Reminder on same date when renewed [if not renewed]',
        'subject' => 'Reminder to renew',
        'to' => '{$user_email}'
    ),
    'member_bulk_invite_for_join_the_community'=> array(
        'description'=>'Member Bulk invite for join the community',
        'subject' => 'Join IFSA',
        'to' => '{$user_email}'
    ),
    'fifteen_days_after_expire_date'=> array(
        'description'=>'15 days after member verification expire',
        'subject' => 'IFSA member verification expired',
        'to' => '{$user_email}'
    ),
    'reject_by_lc_admin_email_to_member'=> array(
        'description'=>'Reject by LC Admin email to member',
        'subject' => 'IFSA membership rejected',
        'to' => '{$user_email}'
    ),
    'twentytwo_days_after_expire_date'=> array(
        'description'=>'15 days after member verification expire',
        'subject' => 'IFSA member verification expired',
        'to' => '{$user_email}'
    ),
    'thirty_days_before_renewal_date'=> array(
        'description'=>'30 days before member verification expire',
        'subject' => 'IFSA member verification expired',
        'to' => '{$user_email}'
    ),
    'thirty_days_after_expire_date'=> array(
        'description'=>'30 days after member verification expire',
        'subject' => 'IFSA member verification expired',
        'to' => '{$user_email}'
    ),
    'remove_member_content'=> array(
        'description'=>'LC Admin remove member',
        'subject' => 'Account removed',
        'to' => '{$user_email}'
    ),
    'register_email_user'=> array(
        'description'=>'Email sent to user on successful registration',
        'subject' => '[IFSA] Registered on the Website',
        'to' => '{$user_email}'
    ),
    'register_email_lc_admin' => array(
        'description'=>'Email sent to LC Admin on user registration',
        'subject' => '[IFSA] New user verification pending',
        'to' => '{$lc_admin_email}'
    )
);
