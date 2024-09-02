<?php

use App\Models\Country;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CountryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $countries = ['Afghanistan' => 'AF', 'Åland Islands' => 'AX', 'Albania' => 'AL', 'Algeria' => 'DZ', 'Andorra' => 'AD', 'Angola' => 'AO', 'Anguilla' => 'AI', 'Antigua & Barbuda' => 'AG', 'Argentina' => 'AR', 'Armenia' => 'AM', 'Aruba' => 'AW', 'Australia' => 'AU', 'Austria' => 'AT', 'Azerbaijan' => 'AZ', 'Bahamas' => 'BS', 'Bahrain' => 'BH', 'Bangladesh' => 'BD', 'Barbados' => 'BB', 'Belarus' => 'BY', 'Belgium' => 'BE', 'Belize' => 'BZ', 'Benin' => 'BJ', 'Bermuda' => 'BM', 'Bhutan' => 'BT', 'Bolivia' => 'BO', 'Bosnia & Herzegovina' => 'BA', 'Botswana' => 'BW', 'Brazil' => 'BR', 'British Virgin Islands' => 'VG', 'Brunei' => 'BN', 'Bulgaria' => 'BG', 'Burkina Faso' => 'BF', 'Burundi' => 'BI', 'Cambodia' => 'KH', 'Cameroon' => 'CM', 'Canada' => 'CA', 'Cape Verde' => 'CV', 'Caribbean Netherlands' => 'BQ', 'Cayman Islands' => 'KY', 'Chad' => 'TD', 'Chile' => 'CL', 'China' => 'CN', 'Colombia' => 'CO', 'Comoros' => 'KM', 'Congo - Brazzaville' => 'CG', 'Congo - Kinshasa' => 'CD', 'Cook Islands' => 'CK', 'Costa Rica' => 'CR', 'Côte d’Ivoire' => 'CI', 'Croatia' => 'HR', 'Curaçao' => 'CW', 'Cyprus' => 'CY', 'Czechia' => 'CZ', 'Denmark' => 'DK', 'Djibouti' => 'DJ', 'Dominica' => 'DM', 'Dominican Republic' => 'DO', 'Ecuador' => 'EC', 'Egypt' => 'EG', 'El Salvador' => 'SV', 'Equatorial Guinea' => 'GQ', 'Estonia' => 'EE', 'Eswatini' => 'SZ', 'Ethiopia' => 'ET', 'Faroe Islands' => 'FO', 'Fiji' => 'FJ', 'Finland' => 'FI', 'France' => 'FR', 'French Guiana' => 'GF', 'French Polynesia' => 'PF', 'Gabon' => 'GA', 'Gambia' => 'GM', 'Georgia' => 'GE', 'Germany' => 'DE', 'Ghana' => 'GH', 'Gibraltar' => 'GI', 'Greece' => 'GR', 'Greenland' => 'GL', 'Grenada' => 'GD', 'Guadeloupe' => 'GP', 'Guatemala' => 'GT', 'Guernsey' => 'GG', 'Guinea' => 'GN', 'Guinea-Bissau' => 'GW', 'Guyana' => 'GY', 'Haiti' => 'HT', 'Honduras' => 'HN', 'Hong Kong SAR China' => 'HK', 'Hungary' => 'HU', 'Iceland' => 'IS', 'India' => 'IN', 'Indonesia' => 'ID', 'Iraq' => 'IQ', 'Ireland' => 'IE', 'Isle of Man' => 'IM', 'Israel' => 'IL', 'Italy' => 'IT', 'Jamaica' => 'JM', 'Japan' => 'JP', 'Jersey' => 'JE', 'Jordan' => 'JO', 'Kazakhstan' => 'KZ', 'Kenya' => 'KE', 'Kiribati' => 'KI', 'Kosovo' => 'XK', 'Kuwait' => 'KW', 'Kyrgyzstan' => 'KG', 'Laos' => 'LA', 'Latvia' => 'LV', 'Lebanon' => 'LB', 'Lesotho' => 'LS', 'Liberia' => 'LR', 'Libya' => 'LY', 'Liechtenstein' => 'LI', 'Lithuania' => 'LT', 'Luxembourg' => 'LU', 'Macao SAR China' => 'MO', 'Madagascar' => 'MG', 'Malawi' => 'MW', 'Malaysia' => 'MY', 'Maldives' => 'MV', 'Mali' => 'ML', 'Malta' => 'MT', 'Martinique' => 'MQ', 'Mauritania' => 'MR', 'Mauritius' => 'MU', 'Mayotte' => 'YT', 'Mexico' => 'MX', 'Moldova' => 'MD', 'Monaco' => 'MC', 'Mongolia' => 'MN', 'Montenegro' => 'ME', 'Morocco' => 'MA', 'Mozambique' => 'MZ', 'Myanmar (Burma)' => 'MM', 'Namibia' => 'NA', 'Nepal' => 'NP', 'Netherlands' => 'NL', 'New Caledonia' => 'NC', 'New Zealand' => 'NZ', 'Nicaragua' => 'NI', 'Niger' => 'NE', 'Nigeria' => 'NG', 'North Macedonia' => 'MK', 'Norway' => 'NO', 'Oman' => 'OM', 'Pakistan' => 'PK', 'Palestinian Territories' => 'PS', 'Panama' => 'PA', 'Papua New Guinea' => 'PG', 'Paraguay' => 'PY', 'Peru' => 'PE', 'Philippines' => 'PH', 'Poland' => 'PL', 'Portugal' => 'PT', 'Qatar' => 'QA', 'Réunion' => 'RE', 'Romania' => 'RO', 'Russia' => 'RU', 'Rwanda' => 'RW', 'Samoa' => 'WS', 'San Marino' => 'SM', 'São Tomé & Príncipe' => 'ST', 'Saudi Arabia' => 'SA', 'Senegal' => 'SN', 'Serbia' => 'RS', 'Seychelles' => 'SC', 'Sierra Leone' => 'SL', 'Singapore' => 'SG', 'Sint Maarten' => 'SX', 'Slovakia' => 'SK', 'Slovenia' => 'SI', 'Solomon Islands' => 'SB', 'Somalia' => 'SO', 'South Africa' => 'ZA', 'South Korea' => 'KR', 'South Sudan' => 'SS', 'Spain' => 'ES', 'Sri Lanka' => 'LK', 'St. Kitts & Nevis' => 'KN', 'St. Lucia' => 'LC', 'St. Martin' => 'MF', 'St. Vincent & Grenadines' => 'VC', 'Sudan' => 'SD', 'Suriname' => 'SR', 'Sweden' => 'SE', 'Switzerland' => 'CH', 'Taiwan' => 'TW', 'Tajikistan' => 'TJ', 'Tanzania' => 'TZ', 'Thailand' => 'TH', 'Timor-Leste' => 'TL', 'Togo' => 'TG', 'Tonga' => 'TO', 'Trinidad & Tobago' => 'TT', 'Tunisia' => 'TN', 'Turkey' => 'TR', 'Turkmenistan' => 'TM', 'Turks & Caicos Islands' => 'TC', 'Uganda' => 'UG', 'Ukraine' => 'UA', 'United Arab Emirates' => 'AE', 'United Kingdom' => 'GB', 'United States' => 'US', 'Uruguay' => 'UY', 'Uzbekistan' => 'UZ', 'Vanuatu' => 'VU', 'Venezuela' => 'VE', 'Vietnam' => 'VN', 'Wallis & Futuna' => 'WF', 'Yemen' => 'YE', 'Zambia' => 'ZM', 'Zimbabwe' => 'ZW'];
        foreach ($countries as $key=>$val){
            Country::updateOrcreate([
                'name' => $key,
                'code' => $val,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ], [
                'name' => $key,
                'code' => $val,
                'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"),
            ]);
        }
    }
}
