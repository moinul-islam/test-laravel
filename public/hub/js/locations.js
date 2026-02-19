// ─── Location Data ────────────────────────────────────────────────
// Structure: { Country: { State: [cities...] } }
const LOC_DATA = {

"Bangladesh": {
  "Dhaka Division": ["Dhaka","Narayanganj","Gazipur","Savar","Tongi","Narsingdi","Tangail","Faridpur","Madaripur","Manikganj","Munshiganj","Rajbari","Shariatpur","Gopalganj"],
  "Chittagong Division": ["Chittagong","Cox's Bazar","Comilla","Feni","Brahmanbaria","Noakhali","Chandpur","Lakshmipur","Rangamati","Khagrachhari","Bandarban","Teknaf","Sitakunda"],
  "Sylhet Division": ["Sylhet","Moulvibazar","Habiganj","Sunamganj"],
  "Rajshahi Division": ["Rajshahi","Chapai Nawabganj","Naogaon","Natore","Pabna","Sirajganj","Bogra","Joypurhat"],
  "Khulna Division": ["Khulna","Jessore","Satkhira","Bagerhat","Narail","Magura","Jhenaidah","Chuadanga","Meherpur","Kushtia"],
  "Barisal Division": ["Barisal","Bhola","Jhalokati","Patuakhali","Pirojpur","Barguna"],
  "Rangpur Division": ["Rangpur","Dinajpur","Gaibandha","Kurigram","Lalmonirhat","Nilphamari","Panchagarh","Thakurgaon"],
  "Mymensingh Division": ["Mymensingh","Netrokona","Sherpur","Jamalpur"]
},

"India": {
  "West Bengal": ["Kolkata","Howrah","Siliguri","Asansol","Durgapur","Bardhaman","Malda","Baharampur","Krishnanagar","Darjeeling","Jalpaiguri","Cooch Behar"],
  "Delhi": ["New Delhi","Dwarka","Rohini","Shahdara","Narela","Noida","Gurugram"],
  "Maharashtra": ["Mumbai","Pune","Nagpur","Thane","Nashik","Aurangabad","Solapur","Navi Mumbai","Kolhapur","Amravati"],
  "Karnataka": ["Bengaluru","Mysuru","Hubli","Mangaluru","Belagavi","Kalaburagi","Davanagere","Shivamogga"],
  "Tamil Nadu": ["Chennai","Coimbatore","Madurai","Tiruchirappalli","Salem","Tirunelveli","Tiruppur","Vellore","Erode"],
  "Uttar Pradesh": ["Lucknow","Kanpur","Agra","Varanasi","Meerut","Allahabad","Ghaziabad","Noida","Aligarh","Bareilly"],
  "Gujarat": ["Ahmedabad","Surat","Vadodara","Rajkot","Bhavnagar","Jamnagar","Gandhinagar","Junagadh"],
  "Rajasthan": ["Jaipur","Jodhpur","Udaipur","Kota","Bikaner","Ajmer","Alwar","Bharatpur"],
  "Madhya Pradesh": ["Bhopal","Indore","Jabalpur","Gwalior","Ujjain","Rewa","Satna","Dewas"],
  "Bihar": ["Patna","Gaya","Muzaffarpur","Bhagalpur","Darbhanga","Arrah","Begusarai","Katihar"],
  "Punjab": ["Ludhiana","Amritsar","Jalandhar","Patiala","Bathinda","Mohali","Pathankot"],
  "Haryana": ["Faridabad","Gurgaon","Panipat","Ambala","Rohtak","Hisar","Karnal","Sonipat"],
  "Kerala": ["Thiruvananthapuram","Kochi","Kozhikode","Thrissur","Kollam","Kannur","Alappuzha","Palakkad"],
  "Andhra Pradesh": ["Visakhapatnam","Vijayawada","Guntur","Nellore","Kurnool","Rajahmundry","Tirupati"],
  "Telangana": ["Hyderabad","Warangal","Nizamabad","Karimnagar","Khammam","Ramagundam"],
  "Odisha": ["Bhubaneswar","Cuttack","Rourkela","Brahmapur","Sambalpur","Puri","Balasore"],
  "Assam": ["Guwahati","Silchar","Dibrugarh","Jorhat","Nagaon","Tinsukia","Tezpur"],
  "Jharkhand": ["Ranchi","Jamshedpur","Dhanbad","Bokaro","Deoghar","Hazaribagh"],
  "Chhattisgarh": ["Raipur","Bhilai","Bilaspur","Korba","Durg","Rajnandgaon"],
  "Uttarakhand": ["Dehradun","Haridwar","Roorkee","Haldwani","Rudrapur","Rishikesh"],
  "Himachal Pradesh": ["Shimla","Dharamsala","Solan","Mandi","Kullu","Manali"]
},

"Pakistan": {
  "Punjab": ["Lahore","Faisalabad","Rawalpindi","Gujranwala","Multan","Sialkot","Bahawalpur","Sargodha","Sheikhupura"],
  "Sindh": ["Karachi","Hyderabad","Sukkur","Larkana","Nawabshah","Mirpurkhas","Jacobabad"],
  "Khyber Pakhtunkhwa": ["Peshawar","Mardan","Mingora","Kohat","Abbottabad","Mansehra"],
  "Balochistan": ["Quetta","Gwadar","Turbat","Khuzdar","Hub","Chaman"],
  "Islamabad Capital Territory": ["Islamabad"],
  "Azad Kashmir": ["Muzaffarabad","Mirpur","Rawalakot","Bagh"],
  "Gilgit-Baltistan": ["Gilgit","Skardu","Chilas"]
},

"United States": {
  "California": ["Los Angeles","San Francisco","San Diego","San Jose","Sacramento","Oakland","Fresno","Long Beach","Bakersfield","Anaheim","Santa Ana","Riverside","Stockton","Irvine"],
  "Texas": ["Houston","San Antonio","Dallas","Austin","Fort Worth","El Paso","Arlington","Corpus Christi","Plano","Lubbock","Laredo","Irving","Garland","Frisco"],
  "New York": ["New York City","Buffalo","Rochester","Yonkers","Syracuse","Albany","Brooklyn","Queens","Manhattan","Bronx","Staten Island"],
  "Florida": ["Jacksonville","Miami","Tampa","Orlando","St. Petersburg","Tallahassee","Fort Lauderdale","Hialeah","Pembroke Pines","Cape Coral","Gainesville"],
  "Illinois": ["Chicago","Aurora","Joliet","Naperville","Rockford","Springfield","Elgin","Peoria","Waukegan","Champaign"],
  "Pennsylvania": ["Philadelphia","Pittsburgh","Allentown","Erie","Reading","Scranton","Bethlehem","Lancaster","Harrisburg"],
  "Ohio": ["Columbus","Cleveland","Cincinnati","Toledo","Akron","Dayton","Parma","Canton","Youngstown"],
  "Georgia": ["Atlanta","Augusta","Columbus","Savannah","Athens","Sandy Springs","Roswell","Macon"],
  "North Carolina": ["Charlotte","Raleigh","Greensboro","Durham","Winston-Salem","Fayetteville","Cary","Wilmington","Asheville"],
  "Michigan": ["Detroit","Grand Rapids","Warren","Sterling Heights","Ann Arbor","Lansing","Flint","Dearborn"],
  "New Jersey": ["Newark","Jersey City","Paterson","Elizabeth","Trenton","Edison","Woodbridge","Lakewood"],
  "Virginia": ["Virginia Beach","Norfolk","Chesapeake","Richmond","Newport News","Alexandria","Hampton","Roanoke"],
  "Washington": ["Seattle","Spokane","Tacoma","Vancouver","Bellevue","Kent","Everett","Renton","Bellingham"],
  "Arizona": ["Phoenix","Tucson","Mesa","Chandler","Scottsdale","Glendale","Gilbert","Tempe","Peoria"],
  "Massachusetts": ["Boston","Worcester","Springfield","Lowell","Cambridge","New Bedford","Brockton","Quincy"],
  "Tennessee": ["Nashville","Memphis","Knoxville","Chattanooga","Clarksville","Murfreesboro","Franklin"],
  "Indiana": ["Indianapolis","Fort Wayne","Evansville","South Bend","Carmel","Hammond","Bloomington"],
  "Missouri": ["Kansas City","St. Louis","Springfield","Independence","Columbia","Lee's Summit"],
  "Maryland": ["Baltimore","Frederick","Rockville","Gaithersburg","Bowie","Annapolis","College Park"],
  "Wisconsin": ["Milwaukee","Madison","Green Bay","Kenosha","Racine","Appleton","Waukesha"],
  "Colorado": ["Denver","Colorado Springs","Aurora","Fort Collins","Lakewood","Thornton","Arvada","Westminster"],
  "Minnesota": ["Minneapolis","Saint Paul","Rochester","Duluth","Bloomington","Brooklyn Park","Plymouth"],
  "Nevada": ["Las Vegas","Henderson","Reno","North Las Vegas","Sparks","Carson City"],
  "Louisiana": ["New Orleans","Baton Rouge","Shreveport","Metairie","Lafayette","Lake Charles"],
  "Oregon": ["Portland","Salem","Eugene","Gresham","Hillsboro","Beaverton","Bend","Medford"],
  "Oklahoma": ["Oklahoma City","Tulsa","Norman","Broken Arrow","Lawton","Edmond"],
  "Kentucky": ["Louisville","Lexington","Bowling Green","Owensboro","Covington","Richmond"],
  "Connecticut": ["Bridgeport","New Haven","Hartford","Stamford","Waterbury","Norwalk","Danbury"],
  "Utah": ["Salt Lake City","West Valley City","Provo","West Jordan","Orem","Sandy","Ogden"],
  "Iowa": ["Des Moines","Cedar Rapids","Davenport","Sioux City","Iowa City","Waterloo","Ames"],
  "Arkansas": ["Little Rock","Fort Smith","Fayetteville","Springdale","Jonesboro","Conway"],
  "Kansas": ["Wichita","Overland Park","Kansas City","Olathe","Topeka","Lawrence"],
  "Mississippi": ["Jackson","Gulfport","Southaven","Hattiesburg","Biloxi","Meridian","Tupelo"],
  "New Mexico": ["Albuquerque","Las Cruces","Rio Rancho","Santa Fe","Roswell","Farmington"],
  "Nebraska": ["Omaha","Lincoln","Bellevue","Grand Island","Kearney","Fremont"],
  "West Virginia": ["Charleston","Huntington","Morgantown","Parkersburg","Wheeling"],
  "Idaho": ["Boise","Nampa","Meridian","Idaho Falls","Pocatello","Caldwell"],
  "Hawaii": ["Honolulu","Pearl City","Hilo","Kailua","Kaneohe"],
  "New Hampshire": ["Manchester","Nashua","Concord","Derry","Dover","Rochester"],
  "Maine": ["Portland","Lewiston","Bangor","South Portland","Auburn"],
  "Montana": ["Billings","Missoula","Great Falls","Bozeman","Butte","Helena"],
  "Rhode Island": ["Providence","Cranston","Warwick","Pawtucket","East Providence"],
  "Delaware": ["Wilmington","Dover","Newark","Middletown","Smyrna"],
  "South Dakota": ["Sioux Falls","Rapid City","Aberdeen","Brookings","Watertown"],
  "North Dakota": ["Fargo","Bismarck","Grand Forks","Minot","West Fargo"],
  "Alaska": ["Anchorage","Fairbanks","Juneau","Sitka","Ketchikan"],
  "Vermont": ["Burlington","South Burlington","Rutland","Barre","Montpelier"],
  "Wyoming": ["Cheyenne","Casper","Laramie","Gillette","Rock Springs"]
},

"United Kingdom": {
  "England": ["London","Birmingham","Manchester","Leeds","Liverpool","Sheffield","Bristol","Newcastle","Nottingham","Leicester","Coventry","Bradford","Plymouth","Southampton","Reading","Derby"],
  "Scotland": ["Glasgow","Edinburgh","Aberdeen","Dundee","Inverness","Stirling","Perth","Falkirk"],
  "Wales": ["Cardiff","Swansea","Newport","Wrexham","Barry","Bridgend"],
  "Northern Ireland": ["Belfast","Derry","Lisburn","Newtownabbey","Ballymena","Armagh"]
},

"Canada": {
  "Ontario": ["Toronto","Ottawa","Mississauga","Brampton","Hamilton","London","Markham","Vaughan","Kitchener","Windsor","Richmond Hill","Oakville","Burlington","Oshawa"],
  "Quebec": ["Montreal","Quebec City","Laval","Gatineau","Longueuil","Sherbrooke","Saguenay"],
  "British Columbia": ["Vancouver","Surrey","Burnaby","Richmond","Kelowna","Abbotsford","Coquitlam","Victoria","Kamloops"],
  "Alberta": ["Calgary","Edmonton","Red Deer","Lethbridge","St. Albert","Medicine Hat","Grande Prairie","Airdrie"],
  "Manitoba": ["Winnipeg","Brandon","Steinbach","Thompson"],
  "Saskatchewan": ["Saskatoon","Regina","Prince Albert","Moose Jaw"],
  "Nova Scotia": ["Halifax","Sydney","Dartmouth","Truro"],
  "New Brunswick": ["Moncton","Saint John","Fredericton","Miramichi"],
  "Newfoundland and Labrador": ["St. John's","Mount Pearl","Corner Brook"],
  "Prince Edward Island": ["Charlottetown","Summerside","Stratford"]
},

"Australia": {
  "New South Wales": ["Sydney","Newcastle","Wollongong","Maitland","Wagga Wagga","Albury","Tamworth","Orange","Dubbo","Coffs Harbour"],
  "Victoria": ["Melbourne","Geelong","Ballarat","Bendigo","Shepparton","Mildura","Warrnambool"],
  "Queensland": ["Brisbane","Gold Coast","Sunshine Coast","Townsville","Cairns","Toowoomba","Ipswich","Mackay","Rockhampton"],
  "Western Australia": ["Perth","Fremantle","Bunbury","Mandurah","Geraldton","Albany","Kalgoorlie"],
  "South Australia": ["Adelaide","Mount Gambier","Whyalla","Port Augusta","Port Pirie"],
  "Tasmania": ["Hobart","Launceston","Devonport","Burnie"],
  "Australian Capital Territory": ["Canberra","Belconnen","Tuggeranong","Gungahlin"],
  "Northern Territory": ["Darwin","Alice Springs","Palmerston","Katherine"]
},

"Germany": {
  "Bavaria": ["Munich","Nuremberg","Augsburg","Regensburg","Ingolstadt","Würzburg","Erlangen","Bayreuth"],
  "North Rhine-Westphalia": ["Cologne","Düsseldorf","Dortmund","Essen","Duisburg","Bochum","Wuppertal","Bielefeld","Bonn","Münster"],
  "Baden-Württemberg": ["Stuttgart","Mannheim","Karlsruhe","Freiburg","Heidelberg","Ulm","Heilbronn"],
  "Berlin": ["Berlin","Charlottenburg","Mitte","Kreuzberg","Friedrichshain","Prenzlauer Berg","Pankow"],
  "Hamburg": ["Hamburg","Altona","Wandsbek","Bergedorf","Harburg"],
  "Hesse": ["Frankfurt","Wiesbaden","Kassel","Darmstadt","Hanau","Offenbach","Marburg"],
  "Saxony": ["Leipzig","Dresden","Chemnitz","Zwickau"],
  "Lower Saxony": ["Hanover","Braunschweig","Osnabrück","Wolfsburg","Göttingen","Oldenburg"],
  "Rhineland-Palatinate": ["Mainz","Ludwigshafen","Koblenz","Trier","Kaiserslautern"],
  "Schleswig-Holstein": ["Kiel","Lübeck","Flensburg","Neumünster"]
},

"France": {
  "Île-de-France": ["Paris","Boulogne-Billancourt","Saint-Denis","Argenteuil","Montreuil","Versailles","Nanterre","Créteil","Colombes"],
  "Auvergne-Rhône-Alpes": ["Lyon","Grenoble","Clermont-Ferrand","Saint-Étienne","Annecy","Valence","Villeurbanne"],
  "Nouvelle-Aquitaine": ["Bordeaux","Limoges","Poitiers","Pau","Bayonne","La Rochelle"],
  "Occitanie": ["Toulouse","Montpellier","Nîmes","Perpignan","Béziers","Carcassonne"],
  "Hauts-de-France": ["Lille","Amiens","Roubaix","Tourcoing","Calais","Dunkirk"],
  "Grand Est": ["Strasbourg","Reims","Metz","Nancy","Mulhouse","Colmar"],
  "Provence-Alpes-Côte d'Azur": ["Marseille","Nice","Toulon","Aix-en-Provence","Cannes","Antibes","Avignon"],
  "Pays de la Loire": ["Nantes","Angers","Le Mans","Saint-Nazaire","Laval"],
  "Normandie": ["Rouen","Caen","Le Havre","Cherbourg","Évreux"],
  "Bretagne": ["Rennes","Brest","Quimper","Lorient","Vannes","Saint-Malo"]
},

"Turkey": {
  "Istanbul": ["Istanbul","Kadıköy","Beşiktaş","Fatih","Üsküdar","Beyoğlu","Bakırköy","Ataşehir","Pendik","Ümraniye"],
  "Ankara": ["Ankara","Çankaya","Keçiören","Mamak","Sincan","Yenimahalle"],
  "Izmir": ["İzmir","Bornova","Buca","Karşıyaka","Konak","Bayraklı","Menemen"],
  "Bursa": ["Bursa","Osmangazi","Nilüfer","Gemlik","İnegöl","Mudanya"],
  "Adana": ["Adana","Seyhan","Çukurova","Sarıçam","Yüreğir","Ceyhan"],
  "Antalya": ["Antalya","Alanya","Manavgat","Serik","Kemer","Side"],
  "Konya": ["Konya","Karatay","Selçuklu","Meram","Ereğli","Akşehir"],
  "Gaziantep": ["Gaziantep","Şahinbey","Şehitkamil","Nizip"],
  "Diyarbakır": ["Diyarbakır","Kayapınar","Bağlar","Sur"]
},

"Saudi Arabia": {
  "Riyadh Region": ["Riyadh","Al Kharj","Dawadmi","Al Majma'ah"],
  "Makkah Region": ["Mecca","Jeddah","Ta'if","Rabigh"],
  "Madinah Region": ["Medina","Yanbu","Al-Ula","Badr"],
  "Eastern Province": ["Dammam","Dhahran","Al-Khobar","Al-Ahsa","Jubail","Qatif"],
  "Asir Region": ["Abha","Khamis Mushait","Najran","Bisha"],
  "Tabuk Region": ["Tabuk","Haql","Al-Wajh","Duba"],
  "Qassim Region": ["Buraidah","Unaizah","Ar-Rass"],
  "Jazan Region": ["Jazan","Sabya","Abu Arish"]
},

"United Arab Emirates": {
  "Abu Dhabi": ["Abu Dhabi","Al Ain","Madinat Zayed","Ruwais","Liwa"],
  "Dubai": ["Dubai","Deira","Jumeirah","Bur Dubai","Jebel Ali","Silicon Oasis","Downtown","Marina"],
  "Sharjah": ["Sharjah","Khor Fakkan","Kalba","Dibba Al Hisn"],
  "Ajman": ["Ajman","Masfout","Manama"],
  "Ras Al Khaimah": ["Ras Al Khaimah","Al Jazirah Al Hamra"],
  "Fujairah": ["Fujairah","Dibba Al Fujairah"],
  "Umm Al Quwain": ["Umm Al Quwain"]
},

"Egypt": {
  "Cairo": ["Cairo","Heliopolis","Nasr City","Maadi","New Cairo","6th of October City"],
  "Alexandria": ["Alexandria","Smouha","Sidi Gaber","Agami"],
  "Giza": ["Giza","6th of October","Sheikh Zayed","Imbaba"],
  "Dakahlia": ["Mansoura","Talkha","Shirbeen","Mit Ghamr"],
  "Sharqia": ["Zagazig","10th of Ramadan City","Abu Kabir"],
  "Beheira": ["Damanhur","Kafr El Dawwar","Rosetta"],
  "Aswan": ["Aswan","Edfu","Kom Ombo"],
  "Luxor": ["Luxor","Esna","Armant"],
  "Red Sea": ["Hurghada","Safaga","Marsa Alam"],
  "North Sinai": ["Arish","Sheikh Zuweid","Rafah"],
  "South Sinai": ["Sharm El Sheikh","Dahab","Nuweiba"]
},

"Nigeria": {
  "Lagos": ["Lagos","Ikeja","Badagry","Epe","Ikorodu","Victoria Island","Lekki","Surulere","Apapa"],
  "Kano": ["Kano","Wudil","Gaya","Bichi","Ungogo"],
  "Rivers": ["Port Harcourt","Obio-Akpor","Eleme","Okrika"],
  "Oyo": ["Ibadan","Ogbomosho","Oyo","Iseyin","Saki"],
  "Abuja FCT": ["Abuja","Gwagwalada","Kuje","Bwari"],
  "Anambra": ["Awka","Onitsha","Nnewi","Ekwulobia"],
  "Kaduna": ["Kaduna","Zaria","Kafanchan"],
  "Delta": ["Asaba","Warri","Ughelli","Sapele"],
  "Edo": ["Benin City","Auchi","Ekpoma"],
  "Enugu": ["Enugu","Nsukka","Agbani"]
},

"South Africa": {
  "Gauteng": ["Johannesburg","Pretoria","Ekurhuleni","Soweto","Centurion","Midrand","Randburg"],
  "Western Cape": ["Cape Town","Stellenbosch","George","Paarl","Worcester","Knysna"],
  "KwaZulu-Natal": ["Durban","Pietermaritzburg","Richards Bay","Newcastle","Ladysmith"],
  "Eastern Cape": ["Port Elizabeth","East London","Mthatha","Grahamstown"],
  "Limpopo": ["Polokwane","Tzaneen","Lephalale","Bela-Bela"],
  "Mpumalanga": ["Nelspruit","Witbank","Middelburg","Secunda"],
  "North West": ["Rustenburg","Mahikeng","Klerksdorp","Potchefstroom"],
  "Free State": ["Bloemfontein","Welkom","Sasolburg","Kroonstad"],
  "Northern Cape": ["Kimberley","Upington","Springbok"]
},

"Kenya": {
  "Nairobi": ["Nairobi","Westlands","Eastleigh","Kasarani","Dagoretti","Embakasi","Kibra"],
  "Mombasa": ["Mombasa","Nyali","Bamburi","Likoni","Changamwe"],
  "Kisumu": ["Kisumu","Ahero","Muhoroni","Maseno"],
  "Nakuru": ["Nakuru","Naivasha","Molo","Gilgil","Njoro"],
  "Kiambu": ["Thika","Ruiru","Kiambu Town","Limuru","Kikuyu"],
  "Uasin Gishu": ["Eldoret","Turbo","Moiben"]
},

"Ghana": {
  "Greater Accra": ["Accra","Tema","Ashaiman","Madina","Teshie","Nungua","Dome"],
  "Ashanti": ["Kumasi","Obuasi","Ejisu","Konongo","Mampong"],
  "Central": ["Cape Coast","Elmina","Kasoa","Winneba"],
  "Northern": ["Tamale","Yendi","Savelugu"],
  "Western": ["Sekondi-Takoradi","Tarkwa","Bogoso","Axim"],
  "Volta": ["Ho","Keta","Kpando","Hohoe","Aflao"],
  "Eastern": ["Koforidua","Nkawkaw","Nsawam","Oda"],
  "Brong-Ahafo": ["Sunyani","Techiman","Berekum","Kintampo"]
},

"Brazil": {
  "São Paulo": ["São Paulo","Guarulhos","Campinas","São Bernardo do Campo","Santo André","Osasco","Sorocaba","São José dos Campos","Ribeirão Preto"],
  "Rio de Janeiro": ["Rio de Janeiro","São Gonçalo","Duque de Caxias","Nova Iguaçu","Niterói","Belford Roxo","Petrópolis"],
  "Minas Gerais": ["Belo Horizonte","Contagem","Uberlândia","Juiz de Fora","Betim","Montes Claros"],
  "Bahia": ["Salvador","Feira de Santana","Vitória da Conquista","Camaçari","Juazeiro"],
  "Paraná": ["Curitiba","Londrina","Maringá","Ponta Grossa","Cascavel","Foz do Iguaçu"],
  "Rio Grande do Sul": ["Porto Alegre","Caxias do Sul","Pelotas","Canoas","Santa Maria"],
  "Pernambuco": ["Recife","Caruaru","Olinda","Petrolina","Paulista"],
  "Ceará": ["Fortaleza","Caucaia","Juazeiro do Norte","Maracanaú","Sobral"]
},

"Argentina": {
  "Buenos Aires": ["Buenos Aires","La Plata","Mar del Plata","Quilmes","Lanús","Lomas de Zamora","Merlo","Moreno","Tigre"],
  "Córdoba": ["Córdoba","Villa María","San Francisco","Río Cuarto"],
  "Santa Fe": ["Rosario","Santa Fe","Venado Tuerto","Rafaela"],
  "Mendoza": ["Mendoza","San Rafael","Godoy Cruz","Guaymallén"],
  "Tucumán": ["San Miguel de Tucumán","Tafí Viejo","Concepción"],
  "Salta": ["Salta","Tartagal","Orán"],
  "Neuquén": ["Neuquén","San Martín de los Andes","Zapala"]
},

"Mexico": {
  "Mexico City": ["Mexico City","Iztapalapa","Gustavo A. Madero","Tlalpan","Coyoacán","Xochimilco"],
  "State of Mexico": ["Ecatepec","Netzahualcóyotl","Toluca","Naucalpan","Chimalhuacán","Tlalnepantla"],
  "Jalisco": ["Guadalajara","Zapopan","San Pedro Tlaquepaque","Tonalá","Puerto Vallarta"],
  "Nuevo León": ["Monterrey","Guadalupe","San Nicolás de los Garza","Apodaca","Santa Catarina"],
  "Veracruz": ["Veracruz","Xalapa","Coatzacoalcos","Boca del Río","Poza Rica"],
  "Puebla": ["Puebla","Tehuacán","San Martín Texmelucan","Atlixco"],
  "Guanajuato": ["León","Guanajuato","Irapuato","Celaya","Salamanca"],
  "Baja California": ["Tijuana","Mexicali","Ensenada","Tecate"],
  "Chihuahua": ["Chihuahua","Ciudad Juárez","Delicias","Cuauhtémoc"],
  "Sinaloa": ["Culiacán","Mazatlán","Ahome","Navolato"],
  "Tamaulipas": ["Tampico","Reynosa","Matamoros","Nuevo Laredo","Victoria"],
  "Quintana Roo": ["Cancún","Playa del Carmen","Chetumal","Cozumel","Tulum"]
},

"Japan": {
  "Tokyo": ["Shinjuku","Shibuya","Minato","Chiyoda","Chūō","Sumida","Meguro","Setagaya","Toshima","Nerima","Suginami","Nakano","Shinagawa","Hachioji","Machida"],
  "Osaka": ["Osaka","Sakai","Higashiosaka","Suita","Hirakata","Amagasaki","Neyagawa","Takatsuki"],
  "Kanagawa": ["Yokohama","Kawasaki","Sagamihara","Fujisawa","Yokosuka","Kamakura","Odawara"],
  "Aichi": ["Nagoya","Toyota","Okazaki","Ichinomiya","Kasugai","Toyohashi"],
  "Saitama": ["Saitama","Kawaguchi","Kawagoe","Tokorozawa","Koshigaya","Ageo"],
  "Chiba": ["Chiba","Funabashi","Matsudo","Kashiwa","Ichikawa","Urayasu"],
  "Hyogo": ["Kobe","Himeji","Nishinomiya","Amagasaki","Akashi","Itami"],
  "Hokkaido": ["Sapporo","Asahikawa","Hakodate","Kushiro","Obihiro"],
  "Fukuoka": ["Fukuoka","Kitakyushu","Kurume","Omuta","Iizuka"],
  "Kyoto": ["Kyoto","Uji","Kameoka","Nagaokakyo"],
  "Okinawa": ["Naha","Urasoe","Okinawa City","Uruma"]
},

"South Korea": {
  "Seoul": ["Seoul","Gangnam","Jongno","Jung","Dongdaemun","Mapo","Yongsan","Seodaemun","Eunpyeong","Nowon"],
  "Gyeonggi": ["Suwon","Goyang","Seongnam","Bucheon","Ansan","Anyang","Yongin","Hwaseong","Namyangju"],
  "Busan": ["Busan","Haeundae","Sasang","Suyeong","Nam","Seo","Dongnae","Busanjin"],
  "Incheon": ["Incheon","Namdong","Bupyeong","Yeonsu","Michuhol"],
  "Daegu": ["Daegu","Jung","Dong","Seo","Suseong","Dalseo"],
  "Daejeon": ["Daejeon","Seo","Yuseong","Daedeok"],
  "Gwangju": ["Gwangju","Nam","Seo","Buk","Gwangsan"],
  "Ulsan": ["Ulsan","Nam","Jung","Buk","Dong"],
  "Gangwon": ["Chuncheon","Wonju","Gangneung","Donghae","Sokcho"],
  "Jeju": ["Jeju City","Seogwipo"]
},

"China": {
  "Guangdong": ["Guangzhou","Shenzhen","Dongguan","Foshan","Shantou","Zhuhai","Jiangmen","Zhongshan","Huizhou"],
  "Shandong": ["Jinan","Qingdao","Zibo","Linyi","Weifang","Yantai","Jining"],
  "Jiangsu": ["Nanjing","Suzhou","Wuxi","Xuzhou","Changzhou","Nantong","Yangzhou"],
  "Zhejiang": ["Hangzhou","Ningbo","Wenzhou","Shaoxing","Huzhou","Jiaxing","Jinhua"],
  "Henan": ["Zhengzhou","Luoyang","Xinxiang","Nanyang","Anyang","Kaifeng"],
  "Sichuan": ["Chengdu","Mianyang","Leshan","Nanchong","Zigong","Deyang"],
  "Hubei": ["Wuhan","Yichang","Xiangyang","Shiyan","Jingzhou"],
  "Hunan": ["Changsha","Zhuzhou","Xiangtan","Hengyang","Shaoyang","Yueyang"],
  "Shanghai": ["Shanghai","Pudong","Huangpu","Xuhui","Jing'an","Putuo"],
  "Beijing": ["Beijing","Chaoyang","Haidian","Fengtai","Dongcheng","Xicheng"],
  "Tianjin": ["Tianjin","Tanggu","Binhai","Wuqing"],
  "Liaoning": ["Shenyang","Dalian","Anshan","Fushun","Jinzhou"],
  "Hebei": ["Shijiazhuang","Tangshan","Baoding","Handan","Langfang"]
},

"Indonesia": {
  "Jakarta": ["Central Jakarta","North Jakarta","South Jakarta","East Jakarta","West Jakarta"],
  "West Java": ["Bandung","Bekasi","Depok","Bogor","Cimahi","Sukabumi","Cirebon","Karawang"],
  "East Java": ["Surabaya","Malang","Pasuruan","Sidoarjo","Probolinggo","Mojokerto","Madiun","Kediri"],
  "Central Java": ["Semarang","Surakarta","Salatiga","Pekalongan","Tegal","Magelang"],
  "Bali": ["Denpasar","Singaraja","Gianyar","Tabanan","Klungkung"],
  "North Sumatra": ["Medan","Binjai","Pematangsiantar","Tanjungbalai"],
  "South Sumatra": ["Palembang","Lubuklinggau","Pagar Alam"],
  "Riau": ["Pekanbaru","Dumai","Bengkalis"],
  "East Kalimantan": ["Samarinda","Balikpapan","Bontang"],
  "South Sulawesi": ["Makassar","Palopo","Parepare"]
},

"Malaysia": {
  "Selangor": ["Shah Alam","Petaling Jaya","Klang","Subang Jaya","Ampang Jaya","Kajang","Puchong","Rawang"],
  "Kuala Lumpur": ["Kuala Lumpur","Chow Kit","Bukit Bintang","Setapak","Wangsa Maju","Kepong"],
  "Johor": ["Johor Bahru","Muar","Batu Pahat","Kluang","Segamat","Kulai"],
  "Penang": ["George Town","Bukit Mertajam","Butterworth","Bayan Lepas"],
  "Perak": ["Ipoh","Taiping","Teluk Intan","Sitiawan","Lumut"],
  "Sarawak": ["Kuching","Miri","Sibu","Bintulu"],
  "Sabah": ["Kota Kinabalu","Sandakan","Tawau","Lahad Datu","Keningau"],
  "Pahang": ["Kuantan","Temerloh","Bentong","Raub"],
  "Kedah": ["Alor Setar","Sungai Petani","Kulim","Langkawi"],
  "Negeri Sembilan": ["Seremban","Port Dickson","Nilai"],
  "Melaka": ["Melaka City","Alor Gajah","Jasin"],
  "Terengganu": ["Kuala Terengganu","Kemaman","Dungun"],
  "Kelantan": ["Kota Bharu","Gua Musang","Pasir Mas","Tumpat"],
  "Perlis": ["Kangar","Arau","Padang Besar"]
},

"Philippines": {
  "Metro Manila": ["Manila","Quezon City","Caloocan","Makati","Pasig","Taguig","Mandaluyong","Marikina","Parañaque","Las Piñas","Malabon","Navotas","Valenzuela","Pasay","Muntinlupa","San Juan"],
  "Central Luzon": ["Angeles City","San Fernando","Malolos","Meycauayan","Olongapo","Tarlac City","Cabanatuan","Balanga"],
  "CALABARZON": ["Calamba","Antipolo","Bacoor","Dasmariñas","General Trias","Lucena","Lipa","Batangas City","Santa Rosa"],
  "Central Visayas": ["Cebu City","Mandaue","Lapu-Lapu","Tagbilaran","Dumaguete"],
  "Davao": ["Davao City","Digos","Tagum","Panabo","Mati"],
  "Northern Mindanao": ["Cagayan de Oro","Iligan","Butuan","Gingoog"],
  "Western Visayas": ["Iloilo City","Bacolod","Roxas City"],
  "Bicol": ["Naga City","Legazpi","Iriga","Sorsogon City"]
},

"Russia": {
  "Moscow": ["Moscow","Zelenograd","Troitsk","Shcherbinka"],
  "Saint Petersburg": ["Saint Petersburg","Kronstadt","Kolpino","Pushkin","Peterhof"],
  "Novosibirsk Oblast": ["Novosibirsk","Berdsk","Ob","Iskitim"],
  "Sverdlovsk Oblast": ["Yekaterinburg","Nizhny Tagil","Kamensk-Uralsky","Pervouralsk"],
  "Tatarstan": ["Kazan","Naberezhnye Chelny","Nizhnekamsk","Almetevsk"],
  "Chelyabinsk Oblast": ["Chelyabinsk","Magnitogorsk","Zlatoust","Miass"],
  "Krasnodar Krai": ["Krasnodar","Sochi","Novorossiysk","Armavir"],
  "Rostov Oblast": ["Rostov-on-Don","Taganrog","Shakhty","Novocherkassk"],
  "Bashkortostan": ["Ufa","Sterlitamak","Salavat","Neftekamsk"],
  "Krasnoyarsk Krai": ["Krasnoyarsk","Norilsk","Achinsk"],
  "Samara Oblast": ["Samara","Togliatti","Syzran"],
  "Omsk Oblast": ["Omsk","Tara"]
},

"Iran": {
  "Tehran Province": ["Tehran","Karaj","Shahr-e Qods","Eslamshahr","Shahriar"],
  "Isfahan Province": ["Isfahan","Kashan","Khomeyni Shahr","Najafabad","Shahinshahr"],
  "Fars Province": ["Shiraz","Marvdasht","Jahrom","Fasa","Kazerun"],
  "Khorasan Razavi": ["Mashhad","Neyshabur","Sabzevar","Torbat-e Heydarieh"],
  "East Azerbaijan": ["Tabriz","Maragheh","Shabestar","Marand"],
  "West Azerbaijan": ["Urmia","Khoy","Miandoab","Naghadeh"],
  "Kerman Province": ["Kerman","Rafsanjan","Jiroft","Sirjan","Bam"],
  "Gilan Province": ["Rasht","Anzali","Lahijan","Langerud"],
  "Mazandaran Province": ["Sari","Babol","Amol","Qaemshahr","Noshahr"],
  "Khuzestan Province": ["Ahvaz","Abadan","Dezful","Khorramshahr"]
},

"Spain": {
  "Community of Madrid": ["Madrid","Móstoles","Alcalá de Henares","Fuenlabrada","Leganés","Getafe","Alcorcón","Torrejón de Ardoz"],
  "Catalonia": ["Barcelona","L'Hospitalet de Llobregat","Badalona","Terrassa","Sabadell","Mataró","Santa Coloma de Gramenet","Reus"],
  "Andalusia": ["Seville","Málaga","Córdoba","Granada","Almería","Jerez de la Frontera","Cádiz","Huelva"],
  "Valencia": ["Valencia","Alicante","Elche","Castellón de la Plana","Torrent","Orihuela"],
  "Basque Country": ["Bilbao","San Sebastián","Vitoria-Gasteiz","Barakaldo"],
  "Galicia": ["Vigo","A Coruña","Ourense","Pontevedra","Santiago de Compostela"],
  "Castilla y León": ["Valladolid","Salamanca","Burgos","León","Zamora"],
  "Castilla-La Mancha": ["Albacete","Toledo","Ciudad Real","Cuenca","Guadalajara"],
  "Canary Islands": ["Las Palmas de Gran Canaria","Santa Cruz de Tenerife","La Laguna","Arrecife"],
  "Balearic Islands": ["Palma","Ibiza","Mahón"]
},

"Italy": {
  "Lombardy": ["Milan","Brescia","Bergamo","Monza","Como","Varese","Pavia","Cremona","Mantua"],
  "Lazio": ["Rome","Latina","Frosinone","Viterbo","Rieti"],
  "Campania": ["Naples","Salerno","Caserta","Avellino","Benevento","Pozzuoli"],
  "Sicily": ["Palermo","Catania","Messina","Syracuse","Agrigento","Ragusa","Trapani"],
  "Veneto": ["Venice","Verona","Padua","Vicenza","Treviso","Rovigo","Belluno"],
  "Piedmont": ["Turin","Novara","Asti","Alessandria","Cuneo","Vercelli"],
  "Emilia-Romagna": ["Bologna","Modena","Parma","Reggio Emilia","Ferrara","Rimini","Forlì"],
  "Tuscany": ["Florence","Pisa","Siena","Livorno","Arezzo","Prato","Grosseto"],
  "Puglia": ["Bari","Taranto","Foggia","Lecce","Brindisi","Andria"],
  "Calabria": ["Reggio Calabria","Catanzaro","Cosenza","Crotone","Vibo Valentia"]
},

"Portugal": {
  "Lisbon District": ["Lisbon","Amadora","Sintra","Cascais","Loures","Almada","Odivelas","Setúbal"],
  "Porto District": ["Porto","Gaia","Braga","Matosinhos","Gondomar","Maia","Vila Nova de Famalicão"],
  "Faro District": ["Faro","Portimão","Loulé","Albufeira","Lagoa","Tavira"],
  "Aveiro District": ["Aveiro","Oliveira de Azeméis","Santa Maria da Feira","São João da Madeira"],
  "Coimbra District": ["Coimbra","Leiria","Figueira da Foz","Pombal"],
  "Madeira": ["Funchal","Câmara de Lobos","Machico","Santa Cruz"],
  "Azores": ["Ponta Delgada","Angra do Heroísmo","Horta"]
},

"Netherlands": {
  "North Holland": ["Amsterdam","Haarlem","Alkmaar","Hilversum","Zaandam","Hoofddorp"],
  "South Holland": ["Rotterdam","The Hague","Leiden","Dordrecht","Zoetermeer","Delft","Alphen aan den Rijn"],
  "Utrecht": ["Utrecht","Amersfoort","Nieuwegein","Veenendaal","Zeist"],
  "North Brabant": ["Eindhoven","Tilburg","Breda","s-Hertogenbosch","Helmond","Oss"],
  "Gelderland": ["Nijmegen","Arnhem","Apeldoorn","Ede","Doetinchem"],
  "Overijssel": ["Enschede","Zwolle","Almelo","Deventer","Hengelo"],
  "Friesland": ["Leeuwarden","Sneek","Drachten","Heerenveen"],
  "Groningen": ["Groningen","Assen","Emmen","Hoogeveen"]
},

"Singapore": {
  "Central Region": ["Downtown Core","Marina Bay","Raffles Place","Tanjong Pagar","Outram","Chinatown","Newton","Novena","Orchard","Kallang"],
  "East Region": ["Bedok","Tampines","Pasir Ris","Changi","Paya Lebar","Geylang"],
  "North Region": ["Woodlands","Yishun","Sembawang","Admiralty"],
  "North-East Region": ["Sengkang","Punggol","Hougang","Serangoon","Ang Mo Kio","Bishan","Toa Payoh"],
  "West Region": ["Jurong East","Jurong West","Clementi","Bukit Timah","Choa Chu Kang","Bukit Batok","Boon Lay"]
},

"Sri Lanka": {
  "Western Province": ["Colombo","Dehiwala-Mount Lavinia","Moratuwa","Sri Jayawardenepura Kotte","Kelaniya","Negombo"],
  "Central Province": ["Kandy","Matale","Nuwara Eliya","Hatton"],
  "Southern Province": ["Galle","Matara","Hambantota","Tangalle"],
  "Northern Province": ["Jaffna","Kilinochchi","Mullaitivu","Vavuniya"],
  "Eastern Province": ["Trincomalee","Batticaloa","Ampara","Kalmunai"],
  "North Western Province": ["Kurunegala","Puttalam","Chilaw"],
  "North Central Province": ["Anuradhapura","Polonnaruwa"],
  "Uva Province": ["Badulla","Monaragala","Bandarawela"],
  "Sabaragamuwa Province": ["Ratnapura","Kegalle"]
},

"Nepal": {
  "Bagmati Province": ["Kathmandu","Lalitpur","Bhaktapur","Birgunj","Hetauda","Bharatpur","Kirtipur"],
  "Gandaki Province": ["Pokhara","Baglung","Waling","Gorkha","Besisahar"],
  "Lumbini Province": ["Butwal","Bhairahawa","Tulsipur","Nepalgunj","Kapilvastu"],
  "Madhesh Province": ["Janakpur","Birganj","Rajbiraj","Lahan","Dhanusha"],
  "Koshi Province": ["Biratnagar","Dharan","Itahari","Inaruwa","Birtamode"],
  "Karnali Province": ["Birendranagar","Jumla","Dailekh","Dolpo"],
  "Sudurpashchim Province": ["Dhangadhi","Mahendranagar","Tikapur","Dipayal"]
},

"Myanmar": {
  "Yangon Region": ["Yangon","Mandalay","Naypyidaw","Pathein","Mawlamyine","Myeik"],
  "Mandalay Region": ["Mandalay","Meiktila","Sagaing","Monywa","Shwebo"],
  "Ayeyarwady Region": ["Pathein","Hinthada","Myaungmya","Pyapon"],
  "Sagaing Region": ["Sagaing","Monywa","Shwebo","Kale"],
  "Bago Region": ["Bago","Toungoo","Pyay","Taungoo"],
  "Mon State": ["Mawlamyine","Thaton","Martaban","Ye"],
  "Shan State": ["Taunggyi","Lashio","Kengtung","Loikaw"],
  "Kachin State": ["Myitkyina","Bhamo","Putao"]
},

"Vietnam": {
  "Hanoi": ["Hanoi","Ba Dinh","Hoan Kiem","Tay Ho","Long Bien","Cau Giay","Dong Da","Hai Ba Trung","Hoang Mai","Thanh Xuan"],
  "Ho Chi Minh City": ["Ho Chi Minh City","District 1","District 3","District 5","District 7","Binh Thanh","Phu Nhuan","Tan Binh","Tan Phu","Go Vap"],
  "Da Nang": ["Da Nang","Hai Chau","Cam Le","Ngu Hanh Son","Lien Chieu","Son Tra"],
  "Hai Phong": ["Hai Phong","Hong Bang","Le Chan","Ngo Quyen","Kien An"],
  "Can Tho": ["Can Tho","Ninh Kieu","Binh Thuy","Cai Rang","O Mon"],
  "Nghe An": ["Vinh","Cua Lo","Thai Hoa","Nghia Dan"],
  "Binh Duong": ["Thu Dau Mot","Di An","Thuan An","Ben Cat","Tan Uyen"],
  "Dong Nai": ["Bien Hoa","Long Khanh","Nhon Trach","Long Thanh"]
},

"Thailand": {
  "Bangkok": ["Bangkok","Chatuchak","Bang Khen","Bang Na","Khlong San","Lat Phrao","Min Buri","Phra Nakhon","Ratchathewi","Pathum Wan"],
  "Chiang Mai": ["Chiang Mai","Chiang Rai","Mae Hong Son","Lampang","Lamphun"],
  "Phuket": ["Phuket","Patong","Karon","Kata","Rawai","Chalong"],
  "Chonburi": ["Pattaya","Chonburi","Si Racha","Bang Saen","Laem Chabang"],
  "Nonthaburi": ["Nonthaburi","Bang Yai","Pak Kret","Bang Bua Thong"],
  "Khon Kaen": ["Khon Kaen","Udon Thani","Ubon Ratchathani","Nakhon Ratchasima"],
  "Songkhla": ["Hat Yai","Songkhla","Surat Thani","Trang","Nakhon Si Thammarat"]
},

"Ethiopia": {
  "Addis Ababa": ["Addis Ababa","Bole","Kolfe Keranio","Yeka","Gullele","Arada"],
  "Oromia": ["Adama","Jimma","Dire Dawa","Bishoftu","Shashamane","Harar"],
  "Amhara": ["Bahir Dar","Gondar","Dessie","Debre Birhan","Debre Markos"],
  "Tigray": ["Mekelle","Axum","Adigrat","Shire"],
  "SNNPR": ["Hawassa","Arba Minch","Wolayita Sodo","Dilla"],
  "Somali": ["Jijiga","Dire Dawa","Kebri Dehar"],
  "Afar": ["Semera","Dire Dawa","Logia"]
},

"Tanzania": {
  "Dar es Salaam": ["Dar es Salaam","Temeke","Kinondoni","Ilala","Ubungo","Kigamboni"],
  "Zanzibar": ["Zanzibar City","Wete","Chake Chake","Mkoani"],
  "Mwanza": ["Mwanza","Musoma","Shinyanga","Bukoba"],
  "Arusha": ["Arusha","Moshi","Kilimanjaro","Babati"],
  "Dodoma": ["Dodoma","Singida","Kondoa"],
  "Mbeya": ["Mbeya","Tukuyu","Iringa","Njombe"]
},

"Uganda": {
  "Central Region": ["Kampala","Entebbe","Jinja","Mukono","Kayunga","Masaka","Mubende"],
  "Eastern Region": ["Mbale","Tororo","Soroti","Iganga","Kamuli","Busia"],
  "Northern Region": ["Gulu","Lira","Arua","Kitgum","Apac","Moroto"],
  "Western Region": ["Mbarara","Kabale","Kasese","Hoima","Fort Portal","Masindi"]
},

"Morocco": {
  "Casablanca-Settat": ["Casablanca","Mohammedia","El Jadida","Settat","Berrechid"],
  "Rabat-Salé-Kénitra": ["Rabat","Salé","Kénitra","Témara","Skhirat"],
  "Marrakech-Safi": ["Marrakech","Safi","El Kelaa des Sraghna","Essaouira","Youssoufia"],
  "Fès-Meknès": ["Fès","Meknès","Taza","Sefrou","Boulemane"],
  "Tanger-Tétouan-Al Hoceïma": ["Tangier","Tetouan","Al Hoceima","Larache","Asilah"],
  "Souss-Massa": ["Agadir","Tiznit","Taroudant","Inezgane","Ait Melloul"],
  "Oriental": ["Oujda","Nador","Taourirt","Berkane","Jerada"],
  "Drâa-Tafilalet": ["Ouarzazate","Errachidia","Zagora","Tinghir"]
},

"Algeria": {
  "Algiers": ["Algiers","Bab El Oued","Kouba","Hussein Dey","El Harrach","Bir Mourad Raïs"],
  "Oran": ["Oran","Mostaganem","Sidi Bel Abbès","Ain Témouchent","Relizane"],
  "Constantine": ["Constantine","El Khroub","Ain Smara","Hamma Bouziane"],
  "Annaba": ["Annaba","Skikda","El Tarf","Guelma"],
  "Sétif": ["Sétif","M'Sila","Bordj Bou Arreridj","Jijel","Béjaïa"],
  "Blida": ["Blida","Boumerdes","Tipaza","Médéa"],
  "Tlemcen": ["Tlemcen","Naama","Aïn Témouchent","Ghazaouet"]
},

"Tunisia": {
  "Tunis": ["Tunis","Ariana","Ben Arous","Manouba","La Marsa","Carthage"],
  "Sfax": ["Sfax","El Aïn","Sakiet Ezzit","Thyna"],
  "Sousse": ["Sousse","Monastir","Mahdia","Msaken"],
  "Bizerte": ["Bizerte","Menzel Bourguiba","Mateur","Ras Jebel"],
  "Nabeul": ["Nabeul","Hammamet","Kelibia","Dar Chaabane"],
  "Gabès": ["Gabès","Médenine","Tataouine","Ben Gardane"],
  "Kairouan": ["Kairouan","Kasserine","Sbeitla","Sidi Bouzid"]
},

"Sudan": {
  "Khartoum": ["Khartoum","Omdurman","Khartoum North","Bahri","Jebel Awlia"],
  "Northern Darfur": ["El Fasher","Kutum","Kebkabiya","Tawila"],
  "Blue Nile": ["Singa","Damazin","Roseires"],
  "Kassala": ["Kassala","Halfa","New Halfa","Wad Madani"],
  "Red Sea": ["Port Sudan","Suakin","Tokar"]
},

"Iraq": {
  "Baghdad": ["Baghdad","Sadr City","Adhamiyah","Karkh","Rusafa","Kadhimiya"],
  "Basra": ["Basra","Zubayr","Abu Al-Khaseeb","Qurna"],
  "Erbil": ["Erbil","Soran","Koya","Shaqlawa"],
  "Mosul (Nineveh)": ["Mosul","Sinjar","Telafar","Hamdaniya"],
  "Sulaymaniyah": ["Sulaymaniyah","Halabja","Ranya","Kalar"],
  "Kirkuk": ["Kirkuk","Tuz Khurmatu","Daquq"],
  "Anbar": ["Ramadi","Fallujah","Haditha","Hit"]
},

"Jordan": {
  "Amman": ["Amman","Zarqa","Russeifa","Madaba","Mafraq","Jubeiha"],
  "Irbid": ["Irbid","Ar Ramtha","Al Husn","Sahel Haoran"],
  "Zarqa": ["Zarqa","Russeifa","Azraq"],
  "Aqaba": ["Aqaba","Wadi Musa","Quweira"],
  "Karak": ["Karak","Al Muwaqqar","Ghawr al Safi"],
  "Ma'an": ["Ma'an","Wadi Musa","Aqaba"]
},

"Lebanon": {
  "Beirut": ["Beirut","Hamra","Verdun","Achrafieh","Dahieh","Ras Beirut"],
  "Mount Lebanon": ["Jounieh","Baabda","Jdeidet El Metn","Zouk Mosbeh","Dbayeh","Byblos"],
  "North Lebanon": ["Tripoli","Zgharta","Batroun","Chekka","Akkar"],
  "South Lebanon": ["Sidon","Tyre","Nabatieh","Bint Jbeil"],
  "Bekaa": ["Zahlé","Baalbek","Hermel","Chtaura"],
  "Akkar": ["Halba","Kobayat","Andqet"]
},

"Kuwait": {
  "Kuwait City": ["Kuwait City","Salmiya","Farwaniya","Hawalli","Rumaithiya","Bayan"],
  "Al Ahmadi": ["Ahmadi","Fahaheel","Mangaf","Mahboula","Fintas"],
  "Al Jahra": ["Jahra","Sulaibiya","Abdally"],
  "Mubarak Al-Kabeer": ["Mubarak Al-Kabeer","Sabah Al-Salem","Abu Fatira"]
},

"Qatar": {
  "Doha": ["Doha","West Bay","Al Sadd","Msheireb","Al Rayyan","Al Wakrah"],
  "Al Rayyan": ["Al Rayyan","Al Aziziya","Muaither","Al Gharrafa"],
  "Al Wakrah": ["Al Wakrah","Al Wukair","Mesaieed"],
  "Al Khor": ["Al Khor","Al Thakhira"],
  "Madinat Ash Shamal": ["Madinat Ash Shamal","Al Ruwais"]
},

"Bahrain": {
  "Capital Governorate": ["Manama","Diplomatic Area","Hidd"],
  "Northern Governorate": ["Muharraq","Hamad Town","A'ali","Jidhafs","Budaiya"],
  "Southern Governorate": ["Riffa","Isa Town","Awali","Zallaq","Sitra","Askar"],
  "Muharraq Governorate": ["Muharraq","Arad","Qalali"]
},

"Oman": {
  "Muscat": ["Muscat","Ruwi","Al Khuwair","Madinat As Sultan Qaboos","Qurm","Wadi Kabir"],
  "Dhofar": ["Salalah","Mirbat","Sadah","Thumrayt"],
  "North Al Batinah": ["Sohar","Al Buraymi","Saham","Shinas"],
  "South Al Batinah": ["Rustaq","Nakhal","Al Awabi"],
  "Al Dakhiliyah": ["Nizwa","Bahla","Adam","Manah"],
  "Ash Sharqiyah": ["Sur","Ibra","Al Mudaybi","Qalhat"]
},

"Pakistan": {
  "Punjab": ["Lahore","Faisalabad","Rawalpindi","Gujranwala","Multan","Sialkot","Bahawalpur","Sargodha","Sheikhupura","Rahim Yar Khan"],
  "Sindh": ["Karachi","Hyderabad","Sukkur","Larkana","Nawabshah","Mirpurkhas","Jacobabad"],
  "Khyber Pakhtunkhwa": ["Peshawar","Mardan","Mingora","Kohat","Abbottabad","Mansehra"],
  "Balochistan": ["Quetta","Gwadar","Turbat","Khuzdar","Hub","Chaman"],
  "Islamabad Capital Territory": ["Islamabad"],
  "Azad Kashmir": ["Muzaffarabad","Mirpur","Rawalakot","Bagh"],
  "Gilgit-Baltistan": ["Gilgit","Skardu","Chilas"]
}

};

// Country list from LOC_DATA keys
const COUNTRIES = Object.keys(LOC_DATA).sort();
