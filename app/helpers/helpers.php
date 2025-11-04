<?php

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

if (!function_exists('getPermissionName')) {
    function getPermissionName($param)
    {
        switch ($param) {
            case 'application':
                return 'Application';
                break;
            case 'upload-mis':
                return 'Upload MIS';
                break;
            case 'bank_mis':
                return 'Bank MIS';
                break;
            case 'settlement':
                return 'Settlement';
                break;
            case 'channel':
                return 'Channel Partner';
                break;
            case 'sales-person':
                return 'Sales Person';
                break;
            case 'staff':
                return 'Staff';
                break;
            case 'bank':
                return 'Bank';
                break;
            case 'product':
                return 'Product';
                break;
            case 'dsa-code':
                return 'Bank Code';
                break;
            case 'bank-target':
                return 'Target';
                break;
            case 'sheet-matching':
                return 'Sheet Matching';
                break;
            case 'services':
                return 'Services';
                break;
            case 'bank-payout':
                return 'Bank Payout';
                break;
            case 'master_code':
                return 'Master Code';
                break;
            default:
                return 'Unknown Permission';
                break;
        }
    }
}

if (!function_exists('getProductName')) {
    function getProductName($param)
    {
        switch ($param) {
            case 'PL':
                return 'Personal Loan';
                break;
            case 'BL':
                return 'Business Loan';
                break;
            case 'HL':
                return 'Home Loan';
                break;
            case 'CC':
                return 'Credit Card';
                break;

            default:
                return 'Unknown Permission';
                break;
        }
    }
}

if (!function_exists('getState')) {

    function getState()
    {
        $state_data = getData();
        $states = json_decode($state_data, true)['states'];
        return $states;
    }
}

if (!function_exists('getData')) {

    function getData()
    {
        return $state_data =
            '{
            "states":[
                {
                    "state":"Andhra Pradesh",
                    "state_code": "AP",
                    "districts":[
                        "Anantapur",
                        "Chittoor",
                        "East Godavari",
                        "Guntur",
                        "Krishna",
                        "Kurnool",
                        "Nellore",
                        "Prakasam",
                        "Srikakulam",
                        "Visakhapatnam",
                        "Vizianagaram",
                        "West Godavari",
                        "YSR Kadapa"
                    ]
                },
                {
                    "state":"Arunachal Pradesh",
                    "state_code": "AR",
                    "districts":[
                        "Tawang",
                        "West Kameng",
                        "East Kameng",
                        "Papum Pare",
                        "Kurung Kumey",
                        "Kra Daadi",
                        "Lower Subansiri",
                        "Upper Subansiri",
                        "West Siang",
                        "East Siang",
                        "Siang",
                        "Upper Siang",
                        "Lower Siang",
                        "Lower Dibang Valley",
                        "Dibang Valley",
                        "Anjaw",
                        "Lohit",
                        "Namsai",
                        "Changlang",
                        "Tirap",
                        "Longding"
                    ]
                },
                {
                    "state":"Assam",
                    "state_code": "AS",
                    "districts":[
                        "Baksa",
                        "Barpeta",
                        "Biswanath",
                        "Bongaigaon",
                        "Cachar",
                        "Charaideo",
                        "Chirang",
                        "Darrang",
                        "Dhemaji",
                        "Dhubri",
                        "Dibrugarh",
                        "Goalpara",
                        "Golaghat",
                        "Hailakandi",
                        "Hojai",
                        "Jorhat",
                        "Kamrup Metropolitan",
                        "Kamrup",
                        "Karbi Anglong",
                        "Karimganj",
                        "Kokrajhar",
                        "Lakhimpur",
                        "Majuli",
                        "Morigaon",
                        "Nagaon",
                        "Nalbari",
                        "Dima Hasao",
                        "Sivasagar",
                        "Sonitpur",
                        "South Salmara-Mankachar",
                        "Tinsukia",
                        "Udalguri",
                        "West Karbi Anglong"
                    ]
                },
                {
                    "state":"Bihar",
                    "state_code": "BR",
                    "districts":[
                        "Araria",
                        "Arwal",
                        "Aurangabad",
                        "Banka",
                        "Begusarai",
                        "Bhagalpur",
                        "Bhojpur",
                        "Buxar",
                        "Darbhanga",
                        "East Champaran (Motihari)",
                        "Gaya",
                        "Gopalganj",
                        "Jamui",
                        "Jehanabad",
                        "Kaimur (Bhabua)",
                        "Katihar",
                        "Khagaria",
                        "Kishanganj",
                        "Lakhisarai",
                        "Madhepura",
                        "Madhubani",
                        "Munger (Monghyr)",
                        "Muzaffarpur",
                        "Nalanda",
                        "Nawada",
                        "Patna",
                        "Purnia (Purnea)",
                        "Rohtas",
                        "Saharsa",
                        "Samastipur",
                        "Saran",
                        "Sheikhpura",
                        "Sheohar",
                        "Sitamarhi",
                        "Siwan",
                        "Supaul",
                        "Vaishali",
                        "West Champaran"
                    ]
                },
                {
                    "state":"Chandigarh (UT)",
                    "state_code": "CH",
                    "districts":[
                        "Chandigarh"
                    ]
                },
                {
                    "state":"Chhattisgarh",
                    "state_code": "CT",
                    "districts":[
                        "Balod",
                        "Baloda Bazar",
                        "Balrampur",
                        "Bastar",
                        "Bemetara",
                        "Bijapur",
                        "Bilaspur",
                        "Dantewada (South Bastar)",
                        "Dhamtari",
                        "Durg",
                        "Gariyaband",
                        "Janjgir-Champa",
                        "Jashpur",
                        "Kabirdham (Kawardha)",
                        "Kanker (North Bastar)",
                        "Kondagaon",
                        "Korba",
                        "Korea (Koriya)",
                        "Mahasamund",
                        "Mungeli",
                        "Narayanpur",
                        "Raigarh",
                        "Raipur",
                        "Rajnandgaon",
                        "Sukma",
                        "Surajpur  ",
                        "Surguja"
                    ]
                },
                {
                    "state":"Dadra and Nagar Haveli (UT)",
                    "state_code": "DN",
                    "districts":[
                        "Dadra & Nagar Haveli"
                    ]
                },
                {
                    "state":"Daman and Diu (UT)",
                    "state_code": "DD",
                    "districts":[
                        "Daman",
                        "Diu"
                    ]
                },
                {
                    "state":"Delhi (NCT)",
                    "state_code": "DL",
                    "districts":[
                        "Central Delhi",
                        "East Delhi",
                        "New Delhi",
                        "North Delhi",
                        "North East  Delhi",
                        "North West  Delhi",
                        "Shahdara",
                        "South Delhi",
                        "South East Delhi",
                        "South West  Delhi",
                        "West Delhi"
                    ]
                },
                {
                    "state":"Goa",
                    "state_code": "GA",
                    "districts":[
                        "North Goa",
                        "South Goa"
                    ]
                },
                {
                    "state":"Gujarat",
                    "state_code": "GJ",
                    "districts":[
                        "Ahmedabad",
                        "Amreli",
                        "Anand",
                        "Aravalli",
                        "Banaskantha (Palanpur)",
                        "Bharuch",
                        "Bhavnagar",
                        "Botad",
                        "Chhota Udepur",
                        "Dahod",
                        "Dangs (Ahwa)",
                        "Devbhoomi Dwarka",
                        "Gandhinagar",
                        "Gir Somnath",
                        "Jamnagar",
                        "Junagadh",
                        "Kachchh",
                        "Kheda (Nadiad)",
                        "Mahisagar",
                        "Mehsana",
                        "Morbi",
                        "Narmada (Rajpipla)",
                        "Navsari",
                        "Panchmahal (Godhra)",
                        "Patan",
                        "Porbandar",
                        "Rajkot",
                        "Sabarkantha (Himmatnagar)",
                        "Surat",
                        "Surendranagar",
                        "Tapi (Vyara)",
                        "Vadodara",
                        "Valsad"
                    ]
                },
                {
                    "state":"Haryana",
                    "state_code": "HR",
                    "districts":[
                        "Ambala",
                        "Bhiwani",
                        "Charkhi Dadri",
                        "Faridabad",
                        "Fatehabad",
                        "Gurgaon",
                        "Hisar",
                        "Jhajjar",
                        "Jind",
                        "Kaithal",
                        "Karnal",
                        "Kurukshetra",
                        "Mahendragarh",
                        "Nuh",
                        "Palwal",
                        "Panchkula",
                        "Panipat",
                        "Rewari",
                        "Rohtak",
                        "Sirsa",
                        "Sonipat",
                        "Yamunanagar"
                    ]
                },
                {
                    "state":"Himachal Pradesh",
                    "state_code": "HP",
                    "districts":[
                        "Bilaspur",
                        "Chamba",
                        "Hamirpur",
                        "Kangra",
                        "Kinnaur",
                        "Kullu",
                        "Lahaul &amp; Spiti",
                        "Mandi",
                        "Shimla",
                        "Sirmaur (Sirmour)",
                        "Solan",
                        "Una"
                    ]
                },
                {
                    "state":"Jammu and Kashmir",
                    "state_code": "JK",
                    "districts":[
                        "Anantnag",
                        "Bandipore",
                        "Baramulla",
                        "Budgam",
                        "Doda",
                        "Ganderbal",
                        "Jammu",
                        "Kathua",
                        "Kishtwar",
                        "Kulgam",
                        "Kupwara",
                        "Poonch",
                        "Pulwama",
                        "Rajouri",
                        "Ramban",
                        "Reasi",
                        "Samba",
                        "Shopian",
                        "Srinagar",
                        "Udhampur"
                    ]
                },
                {
                    "state":"Jharkhand",
                    "state_code": "JH",
                    "districts":[
                        "Bokaro",
                        "Chatra",
                        "Deoghar",
                        "Dhanbad",
                        "Dumka",
                        "East Singhbhum",
                        "Garhwa",
                        "Giridih",
                        "Godda",
                        "Gumla",
                        "Hazaribag",
                        "Jamtara",
                        "Khunti",
                        "Koderma",
                        "Latehar",
                        "Lohardaga",
                        "Pakur",
                        "Palamu",
                        "Ramgarh",
                        "Ranchi",
                        "Sahibganj",
                        "Seraikela-Kharsawan",
                        "Simdega",
                        "West Singhbhum"
                    ]
                },
                {
                    "state":"Karnataka",
                    "state_code": "KA",
                    "districts":[
                        "Bagalkot",
                        "Ballari (Bellary)",
                        "Belagavi (Belgaum)",
                        "Bengaluru (Bangalore) Rural",
                        "Bengaluru (Bangalore) Urban",
                        "Bidar",
                        "Chamarajanagar",
                        "Chikballapur",
                        "Chikkamagaluru (Chikmagalur)",
                        "Chitradurga",
                        "Dakshina Kannada",
                        "Davangere",
                        "Dharwad",
                        "Gadag",
                        "Hassan",
                        "Haveri",
                        "Kalaburagi (Gulbarga)",
                        "Kodagu",
                        "Kolar",
                        "Koppal",
                        "Mandya",
                        "Mysuru (Mysore)",
                        "Raichur",
                        "Ramanagara",
                        "Shivamogga (Shimoga)",
                        "Tumakuru (Tumkur)",
                        "Udupi",
                        "Uttara Kannada (Karwar)",
                        "Vijayapura (Bijapur)",
                        "Yadgir"
                    ]
                },
                {
                    "state":"Kerala",
                    "state_code": "KL",
                    "districts":[
                        "Alappuzha",
                        "Ernakulam",
                        "Idukki",
                        "Kannur",
                        "Kasaragod",
                        "Kollam",
                        "Kottayam",
                        "Kozhikode",
                        "Malappuram",
                        "Palakkad",
                        "Pathanamthitta",
                        "Thiruvananthapuram",
                        "Thrissur",
                        "Wayanad"
                    ]
                },
                {
                    "state":"Lakshadweep (UT)",
                    "state_code": "LD",
                    "districts":[
                        "Agatti",
                        "Amini",
                        "Androth",
                        "Bithra",
                        "Chethlath",
                        "Kavaratti",
                        "Kadmath",
                        "Kalpeni",
                        "Kilthan",
                        "Minicoy"
                    ]
                },
                {
                    "state":"Madhya Pradesh",
                    "state_code": "MP",
                    "districts":[
                        "Agar Malwa",
                        "Alirajpur",
                        "Anuppur",
                        "Ashoknagar",
                        "Balaghat",
                        "Barwani",
                        "Betul",
                        "Bhind",
                        "Bhopal",
                        "Burhanpur",
                        "Chhatarpur",
                        "Chhindwara",
                        "Damoh",
                        "Datia",
                        "Dewas",
                        "Dhar",
                        "Dindori",
                        "Guna",
                        "Gwalior",
                        "Harda",
                        "Hoshangabad",
                        "Indore",
                        "Jabalpur",
                        "Jhabua",
                        "Katni",
                        "Khandwa",
                        "Khargone",
                        "Mandla",
                        "Mandsaur",
                        "Morena",
                        "Narsinghpur",
                        "Neemuch",
                        "Panna",
                        "Raisen",
                        "Rajgarh",
                        "Ratlam",
                        "Rewa",
                        "Sagar",
                        "Satna",
                        "Sehore",
                        "Seoni",
                        "Shahdol",
                        "Shajapur",
                        "Sheopur",
                        "Shivpuri",
                        "Sidhi",
                        "Singrauli",
                        "Tikamgarh",
                        "Ujjain",
                        "Umaria",
                        "Vidisha"
                    ]
                },
                {
                    "state":"Maharashtra",
                    "state_code": "MH",
                    "districts":[
                        "Ahmednagar",
                        "Akola",
                        "Amravati",
                        "Aurangabad",
                        "Beed",
                        "Bhandara",
                        "Buldhana",
                        "Chandrapur",
                        "Dhule",
                        "Gadchiroli",
                        "Gondia",
                        "Hingoli",
                        "Jalgaon",
                        "Jalna",
                        "Kolhapur",
                        "Latur",
                        "Mumbai City",
                        "Mumbai Suburban",
                        "Nagpur",
                        "Nanded",
                        "Nandurbar",
                        "Nashik",
                        "Osmanabad",
                        "Palghar",
                        "Parbhani",
                        "Pune",
                        "Raigad",
                        "Ratnagiri",
                        "Sangli",
                        "Satara",
                        "Sindhudurg",
                        "Solapur",
                        "Thane",
                        "Wardha",
                        "Washim",
                        "Yavatmal"
                    ]
                },
                {
                    "state":"Manipur",
                    "state_code": "MN",
                    "districts":[
                        "Bishnupur",
                        "Chandel",
                        "Churachandpur",
                        "Imphal East",
                        "Imphal West",
                        "Jiribam",
                        "Kakching",
                        "Kamjong",
                        "Kangpokpi",
                        "Noney",
                        "Pherzawl",
                        "Senapati",
                        "Tamenglong",
                        "Tengnoupal",
                        "Thoubal",
                        "Ukhrul"
                    ]
                },
                {
                    "state":"Meghalaya",
                    "state_code": "ML",
                    "districts":[
                        "East Garo Hills",
                        "East Jaintia Hills",
                        "East Khasi Hills",
                        "North Garo Hills",
                        "Ri Bhoi",
                        "South Garo Hills",
                        "South West Garo Hills",
                        "South West Khasi Hills",
                        "West Garo Hills",
                        "West Jaintia Hills",
                        "West Khasi Hills"
                    ]
                },
                {
                    "state":"Mizoram",
                    "state_code": "MZ",
                    "districts":[
                        "Aizawl",
                        "Champhai",
                        "Hnahthial",
                        "Khawzawl",
                        "Kolasib",
                        "Lawngtlai",
                        "Lunglei",
                        "Mamit",
                        "Saiha",
                        "Saitual",
                        "Serchhip"
                    ]
                },
                {
                    "state":"Nagaland",
                    "state_code": "NL",
                    "districts":[
                        "Mon",
                        "Dimapur",
                        "Kiphire",
                        "Kohima",
                        "Longleng",
                        "Mokokchung",
                        "Noklak",
                        "Peren",
                        "Phek",
                        "Tuensang",
                        "Wokha",
                        "Zunheboto"
                    ]
                },
                {
                    "state":"Odisha",
                    "state_code": "OD",
                    "districts":[
                        "Angul",
                        "Balangir",
                        "Balasore",
                        "Bargarh",
                        "Bhadrak",
                        "Boudh",
                        "Cuttack",
                        "Deogarh",
                        "Dhenkanal",
                        "Gajapati",
                        "Ganjam",
                        "Jagatsinghpur",
                        "Jajpur",
                        "Jharsuguda",
                        "Kalahandi",
                        "Kandhamal",
                        "Kendrapara",
                        "Kendujhar (Keonjhar)",
                        "Khordha",
                        "Koraput",
                        "Malkangiri",
                        "Mayurbhanj",
                        "Nabarangpur",
                        "Nayagarh",
                        "Nuapada",
                        "Puri",
                        "Rayagada",
                        "Sambalpur",
                        "Subarnapur (Sonepur)",
                        "Sundargarh"
                    ]
                },
                {
                    "state":"Puducherry (UT)",
                    "state_code": "PY",
                    "districts":[
                        "Karaikal",
                        "Mahe",
                        "Puducherry",
                        "Yanam"
                    ]
                },
                {
                    "state":"Punjab",
                    "state_code": "PB",
                    "districts":[
                        "Amritsar",
                        "Barnala",
                        "Bathinda",
                        "Faridkot",
                        "Fatehgarh Sahib",
                        "Fazilka",
                        "Ferozepur",
                        "Gurdaspur",
                        "Hoshiarpur",
                        "Jalandhar",
                        "Kapurthala",
                        "Ludhiana",
                        "Mansa",
                        "Moga",
                        "Pathankot",
                        "Patiala",
                        "Rupnagar",
                        "Sahibzada Ajit Singh Nagar (Mohali)",
                        "Sangrur",
                        "Shaheed Bhagat Singh Nagar (Nawanshahr)",
                        "Sri Muktsar Sahib",
                        "Tarn Taran"
                    ]
                },
                {
                    "state":"Rajasthan",
                    "state_code": "RJ",
                    "districts":[
                        "Ajmer",
                        "Alwar",
                        "Banswara",
                        "Baran",
                        "Barmer",
                        "Bharatpur",
                        "Bhilwara",
                        "Bikaner",
                        "Bundi",
                        "Chittorgarh",
                        "Churu",
                        "Dausa",
                        "Dholpur",
                        "Dungarpur",
                        "Hanumangarh",
                        "Jaipur",
                        "Jaisalmer",
                        "Jalore",
                        "Jhalawar",
                        "Jhunjhunu",
                        "Jodhpur",
                        "Karauli",
                        "Kota",
                        "Nagaur",
                        "Pali",
                        "Pratapgarh",
                        "Rajsamand",
                        "Sawai Madhopur",
                        "Sikar",
                        "Sirohi",
                        "Sri Ganganagar",
                        "Tonk",
                        "Udaipur"
                    ]
                },
                {
                    "state":"Sikkim",
                    "state_code": "SK",
                    "districts":[
                        "East Sikkim",
                        "North Sikkim",
                        "South Sikkim",
                        "West Sikkim"
                    ]
                },
                {
                    "state":"Tamil Nadu",
                    "state_code": "TN",
                    "districts":[
                        "Ariyalur",
                        "Chengalpattu",
                        "Chennai",
                        "Coimbatore",
                        "Cuddalore",
                        "Dharmapuri",
                        "Dindigul",
                        "Erode",
                        "Kallakurichi",
                        "Kanchipuram",
                        "Kanyakumari",
                        "Karur",
                        "Krishnagiri",
                        "Madurai",
                        "Nagapattinam",
                        "Namakkal",
                        "Nilgiris",
                        "Perambalur",
                        "Pudukkottai",
                        "Ramanathapuram",
                        "Ranipet",
                        "Salem",
                        "Sivaganga",
                        "Tenkasi",
                        "Thanjavur",
                        "Theni",
                        "Thoothukudi (Tuticorin)",
                        "Tiruchirappalli",
                        "Tirunelveli",
                        "Tirupathur",
                        "Tiruppur",
                        "Tiruvallur",
                        "Tiruvannamalai",
                        "Tiruvarur",
                        "Vellore",
                        "Viluppuram",
                        "Virudhunagar"
                    ]
                },
                {
                    "state":"Telangana",
                    "state_code": "TG",
                    "districts":[
                        "Adilabad",
                        "Bhadradri Kothagudem",
                        "Hyderabad",
                        "Jagtial",
                        "Jangaon",
                        "Jayashankar Bhupalpally",
                        "Jogulamba Gadwal",
                        "Kamareddy",
                        "Karimnagar",
                        "Khammam",
                        "Kumuram Bheem",
                        "Mahabubabad",
                        "Mahbubnagar",
                        "Mancherial",
                        "Medak",
                        "Medchal",
                        "Nagarkurnool",
                        "Nalgonda",
                        "Nirmal",
                        "Nizamabad",
                        "Peddapalli",
                        "Rajanna Sircilla",
                        "Rangareddy",
                        "Sangareddy",
                        "Siddipet",
                        "Suryapet",
                        "Vikarabad",
                        "Wanaparthy",
                        "Warangal (Rural)",
                        "Warangal (Urban)",
                        "Yadadri Bhuvanagiri"
                    ]
                },
                {
                    "state":"Tripura",
                    "state_code": "TR",
                    "districts":[
                        "Dhalai",
                        "Gomati",
                        "Khowai",
                        "North Tripura",
                        "Sepahijala",
                        "South Tripura",
                        "Unakoti",
                        "West Tripura"
                    ]
                },
                {
                    "state":"Uttarakhand",
                    "state_code": "UT",
                    "districts":[
                        "Almora",
                        "Bageshwar",
                        "Chamoli",
                        "Champawat",
                        "Dehradun",
                        "Haridwar",
                        "Nainital",
                        "Pauri Garhwal",
                        "Pithoragarh",
                        "Rudraprayag",
                        "Tehri Garhwal",
                        "Udham Singh Nagar",
                        "Uttarkashi"
                    ]
                },
                {
                    "state":"Uttar Pradesh",
                    "state_code": "UP",
                    "districts":[
                        "Agra",
                        "Aligarh",
                        "Allahabad",
                        "Ambedkar Nagar",
                        "Amethi (Chatrapati Sahuji Mahraj Nagar)",
                        "Amroha (J.P. Nagar)",
                        "Auraiya",
                        "Azamgarh",
                        "Baghpat",
                        "Bahraich",
                        "Ballia",
                        "Balrampur",
                        "Banda",
                        "Barabanki",
                        "Bareilly",
                        "Basti",
                        "Bhadohi",
                        "Bijnor",
                        "Budaun",
                        "Bulandshahr",
                        "Chandauli",
                        "Chitrakoot",
                        "Deoria",
                        "Etah",
                        "Etawah",
                        "Faizabad",
                        "Farrukhabad",
                        "Fatehpur",
                        "Firozabad",
                        "Gautam Buddh Nagar",
                        "Ghaziabad",
                        "Ghazipur",
                        "Gonda",
                        "Gorakhpur",
                        "Hamirpur",
                        "Hapur (Panchsheel Nagar)",
                        "Hardoi",
                        "Hathras",
                        "Jalaun",
                        "Jaunpur",
                        "Jhansi",
                        "Kannauj",
                        "Kanpur Dehat",
                        "Kanpur Nagar",
                        "Kasganj",
                        "Kaushambi",
                        "Kushinagar (Padrauna)",
                        "Lakhimpur - Kheri",
                        "Lalitpur",
                        "Lucknow",
                        "Maharajganj",
                        "Mahoba",
                        "Mainpuri",
                        "Mathura",
                        "Mau",
                        "Meerut",
                        "Mirzapur",
                        "Moradabad",
                        "Muzaffarnagar",
                        "Noida",
                        "Pilibhit",
                        "Pratapgarh",
                        "RaeBareli",
                        "Rampur",
                        "Saharanpur",
                        "Sambhal (Bhim Nagar)",
                        "Sant Kabir Nagar",
                        "Shahjahanpur",
                        "Shamali (Prabuddh Nagar)",
                        "Shravasti",
                        "Siddharth Nagar",
                        "Sitapur",
                        "Sonbhadra",
                        "Sultanpur",
                        "Unnao",
                        "Varanasi"
                    ]
                },
                {
                    "state":"West Bengal",
                    "state_code": "WB",
                    "districts":[
                        "Alipurduar",
                        "Bankura",
                        "Birbhum",
                        "Burdwan (Bardhaman)",
                        "Cooch Behar",
                        "Dakshin Dinajpur (South Dinajpur)",
                        "Darjeeling",
                        "Hooghly",
                        "Howrah",
                        "Jalpaiguri",
                        "Kalimpong",
                        "Kolkata",
                        "Malda",
                        "Murshidabad",
                        "Nadia",
                        "North 24 Parganas",
                        "Paschim Medinipur (West Medinipur)",
                        "Purba Medinipur (East Medinipur)",
                        "Purulia",
                        "South 24 Parganas",
                        "Uttar Dinajpur (North Dinajpur)"
                    ]
                }
            ]
        }';
    }
}

if (!function_exists('getStateName')) {

    function getStateName($state_code)
    {
        $state_data = getData();
        $states = json_decode($state_data, true)['states'];
        foreach ($states as $state_info) {
            if ($state_info['state_code'] === $state_code) {
                return $state_info['state'];
            }
        }
        return $states;
    }
}


if (!function_exists('generateEmployeeID')) {
    function generateEmployeeID($stateCode, $district, $firstName)
    {
        // Extract first three letters of the district and first name
        $districtAbbreviation = substr($district, 0, 3);
        $firstNameAbbreviation = substr($firstName, 0, 3);

        // Retrieve the count of employees in the same state and district
        $count = User::where('state', $stateCode)
            ->where('district', $district)
            ->count();

        // Increment the count by 1
        $count++;
        $nextId = 111 * $count;

        // Generate the employee ID
        $employeeID = $stateCode . '_' . $districtAbbreviation . '_' . $firstNameAbbreviation . '_' . $nextId;

        return $employeeID;
    }
}

if (!function_exists('generateStaffID')) {
    function generateStaffID($firstName)
    {
        // Extract first three letters of the district and first name
        $firstNameAbbreviation = substr($firstName, 0, 3);


        // Generate the employee ID
        $employeeID = $firstNameAbbreviation . '_' . rand(111, 999);

        return $employeeID;
    }
}
if (!function_exists('match_excel')) {
    function match_excel($bank_id, $product_id, $excelHeaders)
    {
        $$expectedHeaders = [];
        switch ($bank_id) {
            case '1': {
                    switch ($product_id) {
                        default:
                            $expectedHeaders = [
                                "ZONE Name",
                                "Branch Name",
                                "Appl NO FINAL",
                                "BORNAME",
                                "Scheme GROUP Name",
                                "Sanction Date",
                                "Sanction Loan",
                                "Disbursement Sr. no.",
                                "Disbursement / Reversal Amount",
                                "Disbursement / Reversal Date",
                                "Interest Rate",
                                "HANDOVER DATE",
                                "FIRST Disbursement Date",
                                "LAST Disbursement Date",
                                "LAP/ HL",
                                "DSA CODE",
                                "DSA NAME",
                                "AM SYS CODE",
                                "AM NAME",
                                "Cheque hand over status",
                                "Disb Month"
                            ];
                            break;
                    }
                }
        }

        return $excelHeaders == $expectedHeaders ? "true" : "false";
    }
}

if (!function_exists('checkValueAndSetFlag')) {

    function checkValueAndSetFlag($application, $columnName, $columnValue)
    {
        // if ($db == 'application') {
        DB::table('applications')->where('id', $application->id)->update([
            $columnName . '_is_value' => $columnValue
        ]);
        return trim(strtolower($application->$columnName)) == trim(strtolower($columnValue));
    }
}

if (!function_exists('mis_bank')) {

    function mis_bank()
    {
        $data =  [
            [
                "bank_id" => 1,

            ],
            [

                "bank_name" => "Aavas Financiers Ltd",
                "bank_id" => 2,
            ],
            [

                "bank_name" => "Aditya Birla Finance Limited",
                "bank_id" => 3,
            ],
            [

                "bank_name" => "Aadhar Housing Finance Ltd Digital Bank	",
                "bank_id" => 4,
            ],
            [

                "bank_name" => "Aditya Birla Housing Finance Ltd",
                "bank_id" => 5,
            ],
            [

                "bank_name" => "Aadhar Housing Finance Ltd",
                "bank_id" => 1,
            ],
        ];
    }
}


if (!function_exists('indianNumberFormat')) {

    function indianNumberFormat($num)
    {
        $exploded = explode('.', $num);
        $intPart = $exploded[0];
        $decimalPart = isset($exploded[1]) ? '.' . $exploded[1] : '';

        $lastThree = substr($intPart, -3);
        $otherNumbers = substr($intPart, 0, -3);

        if ($otherNumbers != '') {
            $lastThree = ',' . $lastThree;
        }

        $formatted = preg_replace('/\B(?=(\d{2})+(?!\d))/', ',', $otherNumbers) . $lastThree . $decimalPart;

        return $formatted;
    }
}


if (!function_exists('generateQRCode')) {
    function generateQRCode($data)
    {
        // Use external service to generate PNG without requiring Imagick
        $size = '400x400';
        $url = 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . '&data=' . urlencode((string) $data);
        return $url;
    }
}

if (!function_exists('generateEmployeeCode')) {
    function generateEmployeeCode($stateCode, $district, $firstName, $id)
    {
        // Extract first three letters of the district and first name
        $districtAbbreviation = substr($district, 0, 3);
        $firstNameAbbreviation = substr($firstName, 0, 3);

        $nextId = '000'.$id;

        // Generate the employee ID
        $employeeID = $stateCode . '' . $districtAbbreviation . '' . $firstNameAbbreviation . '' . $nextId;

        return $employeeID;
    }
}