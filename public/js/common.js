$(function() {
    replaceMainPreferencesIcon();
    $(".glpi_tabs").on("tabsload", function(event, ui) {
        doStuff();
    });
});

var replaceMainPreferencesIcon = function()
{
    if (! $("html").hasClass("stuff-added")) {
        $("html").addClass("stuff-added");

        let preferencesMainTab = $('a[href="/front/preference.php?forcetab=User$1"]').get();
        if(preferencesMainTab.length > 0){
            let icon = preferencesMainTab[0].firstChild.firstChild;
            icon.classList.remove("ti-user");
            icon.classList.add("ti-mood-smile");
        }
    }
};
