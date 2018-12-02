var MessageBox = function(id) {
    
    this.messages = new Array();
    this.id = id;
    
    this.add = function(list) {
        if(list == null) return;
        for(obj in list)
            this.messages.push(list[obj]);
        this.update();
    };
    
    this.logInfo = function(msg) {
        this.log("info", msg);
    };
    
    this.logError = function(msg) {
        this.log("error", msg);
    };
    
    this.log = function(type, msg) {
        this.messages.push({ 'type' : type, 'msg' : msg });
        this.update();
        console.log(new Date().toISOString() + " " + type + ": " + msg);
    };

    this.length = function() {
        return this.messages.length;
    }

    this.clear = function() {
        this.messages = new Array();
        this.update();
    };
    
    this.update = function() {
        target = $("#"+this.id);

        target.empty();
        for(var i=0; i<this.messages.length; i++) {
            var html = "";
            var o = this.messages[i];
            template = $("#"+this.id+"_template_"+o.type);
            if(o.type == "error")
            	html += '<span class="glyphicon glyphicon-exclamation-sign pr-2" aria-hidden="true"></span> ';
            if(o.type == "warning")
            	html += '<span class="glyphicon glyphicon-alert pr-2" aria-hidden="true"></span> ';
            if(o.type == "info")
            	html += '<span class="glyphicon glyphicon-info-sign pr-2" aria-hidden="true"></span> ';
            if(o.type == "success")
                html += '<span class="glyphicon glyphicon-ok pr-2" aria-hidden="true"></span> ';
            html += o.msg;
            template.clone().html(html).appendTo(target);
        }
        if(this.messages.length > 0)
            this.display();
        else
            this.display(false);
    };
    
    this.display = function(visible) {
        if(visible == null)
            visible = true;
        else visible = false;
        var box = $("#" + this.id);
        if(visible) {
        	box.css('display', 'block');
        } else {
        	box.css('display', 'none');
        }
    };
    
};

var errorbox = new MessageBox("errorbox");
var infobox = new MessageBox("infobox");


var PasswordHelper = function(passwordid) {
    this.passwordid = passwordid;
    this.goodentropy = 105;
    this.percentval = 0;
    this.qualityval = "";
    
    this.entropy = function(entropy) {
        this.goodentropy = entropy;
    }
    
    this.update = function() {
        var field = document.getElementById(this.passwordid);
        var progress = document.getElementById(this.progressid);

        var word = field.value;
        
        var length = word.length;
        var lower = word.search(".*[a-z]") == 0;
        var upper = word.search(".*[A-Z]") == 0;
        var number = word.search(".*[0-9]") == 0;
        var special = word.search(".*[^A-Za-z0-9]") == 0;

        var alphabet = 0;
        if(lower) { alphabet += 26; }
        if(upper) { alphabet += 26; }
        if(number) { alphabet += 10; }
        if(special) { alphabet += 33; }

        var entropy = length * Math.ceil(Math.log2(alphabet));
        if(alphabet == 0) { entropy = 0; }

        var known = this.is_wellknown(word)
        if(known) { entropy = 0; }
        
        this.qualityval = "very weak";
        if(entropy > 36) { this.qualityval = "weak"; }          // length=6  + upper/lower
        if(entropy > 48) { this.qualityval = "good"; }          // length=8  + upper/lower/number
        if(entropy > 70) { this.qualityval = "strong"; }            // length=10 + upper/lower/number/special
        if(entropy > 105) { this.qualityval = "very strong"; }      // length=15 + upper/lower/number/special
        if(entropy > 128) { this.qualityval = "very strong - 128bit strength"; }    // length=16 + alphabet=256
        if(entropy > 256) { this.qualityval = "very strong - 256bit strength"; }    // length=32 + alphabet=256
        
        this.percentval = Math.max(Math.min(entropy / this.goodentropy * 100, 100), 10);
        
        /*
        this.log("", true);
        this.log("lower: " +lower);
        this.log("upper: " +upper);
        this.log("number: " +number);
        this.log("special: " +special);
        this.log("");
        this.log("alphabet: " +alphabet);
        this.log("length: " +length);
        this.log("entropy: " +entropy);
        this.log("");
        this.log("quality: " +quality);
        this.log("well-known: " +known);
        this.log("percent: " +Math.round(percent));
        */
    }
    
    this.quality = function() {
        return this.qualityval;
    }
    
    this.percent = function() {
        return this.percentval;
    }
    
    this.log = function(message, clear) {
        var debug = document.getElementById("debug");
        if(clear == true)
            debug.innerHTML = "";
        debug.innerHTML = debug.innerHTML + message + "\n";
    }
    
    this.is_wellknown = function(password) {
        // 500 most often used passwords (= very bad!)
        var list = "0 0 1111 11111 111111 11111111 112233 1212 121212 123123 1234 12345 123456 1234567 12345678 1313 131313 2000 2112 2222 232323 3333 4128 4321 4444 5150 5555 654321 6666 666666 6969 696969 7777 777777 7777777 8675309 987654 aaaa aaaaaa abc123 abgrtyu access access14 action albert alex alexis amanda amateur andrea andrew angel angela angels animal anthony apollo apple apples arsenal arthur asdf asdfgh ashley asshole august austin baby badboy bailey banana barney baseball batman beach bear beaver beavis beer bigcock bigdaddy bigdick bigdog bigtits bill billy birdie bitch bitches biteme black blazer blonde blondes blowjob blowme blue bond007 bonnie booboo boobs booger boomer booty boston brandon brandy braves brazil brian bronco broncos bubba buddy bulldog buster butter butthead calvin camaro cameron canada captain carlos carter casper charles charlie cheese chelsea chester chevy chicago chicken chris cocacola cock coffee college compaq computer cookie cool cooper corvette cowboy cowboys cream crystal cumming cumshot cunt dakota dallas daniel danielle dave david debbie dennis diablo diamond dick dirty doctor doggie dolphin dolphins donald dragon dreams driver eagle eagle1 eagles edward einstein enjoy enter eric erotic extreme falcon fender ferrari fire firebird fish fishing florida flower flyers football ford forever frank fred freddy freedom fuck fucked fucker fucking fuckme fuckyou gandalf gateway gators gemini george giants ginger girl girls golden golf golfer gordon great green gregory guitar gunner hammer hannah happy hardcore harley heather hello helpme hentai hockey hooters horney horny hotdog house hunter hunting iceman iloveyou internet iwantu jack jackie jackson jaguar jake james japan jasmine jason jasper jennifer jeremy jessica john johnny johnson jordan joseph joshua juice junior justin kelly kevin killer king kitty knight ladies lakers lauren leather legend letmein little london love lover lovers lucky maddog madison maggie magic magnum marine mark marlboro martin marvin master matrix matt matthew maverick maxwell melissa member mercedes merlin michael michelle mickey midnight mike miller mine mistress money monica monkey monster morgan mother mountain movie muffin murphy music mustang naked nascar nathan naughty ncc1701 newyork nicholas nicole nipple nipples oliver orange ou812 packers panther panties paris parker pass password patrick paul peaches peanut penis pepper peter phantom phoenix player please pookie porn porno porsche power prince princess private purple pussies pussy qazwsx qwert qwerty qwertyui rabbit rachel racing raiders rainbow ranger rangers rebecca redskins redsox redwings richard robert rock rocket rosebud runner rush2112 russia samantha sammy samson sandra saturn scooby scooter scorpio scorpion scott secret sexsex sexy shadow shannon shaved shit sierra silver skippy slayer slut smith smokey snoopy soccer sophie spanky sparky spider squirt srinivas star stars startrek starwars steelers steve steven sticky stupid success suckit summer sunshine super superman surfer swimming sydney taylor teens tennis teresa test tester testing theman thomas thunder thx1138 tiffany tiger tigers tigger time tits tomcat topgun toyota travis trouble trustno1 tucker turtle united vagina victor victoria video viking viper voodoo voyager walter warrior welcome whatever white william willie wilson winner winston winter wizard wolf women xavier xxxx xxxxx xxxxxx xxxxxxxx yamaha yankee yankees yellow young zxcvbn zxcvbnm zzzzzz correcthorsebatterystaple";
        return list.indexOf(password.toLowerCase()) > -1;
    }   
}
