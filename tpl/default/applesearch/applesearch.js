/* START applesearch object */
		
if (!applesearch)	var applesearch = {};
if (!applesearch_base) var applesearch_base = '';

applesearch.init = function (base_location)
{
    // init path to this script
    applesearch_base = base_location;

	// add applesearch css for non-safari, dom-capable browsers
	if ( navigator.userAgent.toLowerCase().indexOf('safari') < 0  && document.getElementById )
	{
		this.clearBtn = false;
		
		// add style sheet if not safari
		var dummy = document.getElementById("dummy_css");
		if (dummy)	dummy.href = applesearch_base + "applesearch.css";
	}
}

// called when on user input - toggles clear fld btn
applesearch.onChange = function (fldID, btnID)
{
	// check whether to show delete button
	var fld = document.getElementById( fldID );
	var btn = document.getElementById( btnID );
	if (fld.value.length > 0 && !this.clearBtn)
	{
		btn.style.background = "white url('" + applesearch_base + "srch_r_f2.gif') no-repeat top left";
		btn.fldID = fldID; // btn remembers it's field
		btn.onclick = this.clearBtnClick;
		this.clearBtn = true;
	} else if (fld.value.length == 0 && this.clearBtn)
	{
		btn.style.background = "white url('" + applesearch_base + "srch_r.gif') no-repeat top left";
		btn.onclick = null;
		this.clearBtn = false;
	}
}


// clears field
applesearch.clearFld = function (fldID,btnID)
{
	var fld = document.getElementById( fldID );
	fld.value = "";
	this.onChange(fldID,btnID);
}

// called by btn.onclick event handler - calls clearFld for this button
applesearch.clearBtnClick = function ()
{
	applesearch.clearFld(this.fldID, this.id);
    var fld = document.getElementById( this.fldID );
    fld.focus();
    document.search.submit();
    //fld.select();
}

/* END applesearch object */