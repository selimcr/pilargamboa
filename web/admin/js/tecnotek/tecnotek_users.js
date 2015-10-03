var Tecnotek = Tecnotek || {};

Tecnotek.Users = {
    List: {
        yesNoFormat: function(value, row, index) {
            return [
                (value)? 'Si':'No',
            ].join('');
        },
        operateFormatter: function(value, row, index){
            return [
                '<a class="edit" href="javascript:void(0)" title="Editar">',
                '<i class="glyphicon glyphicon-edit"></i>',
                '</a>',
                '<a class="delete" href="javascript:void(0)" title="Eliminar">',
                '<i class="glyphicon glyphicon-remove"></i>',
                '</a>'
            ].join('');
        },
        operateEvents: {
            'click .like': function (e, value, row, index) {
                alert('You click like action, row: ' + JSON.stringify(row));
            },
            'click .delete': function (e, value, row, index) {
                if(Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-delete'])){
                    Tecnotek.showPleaseWait();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['delete'], {
                            id:     row.id
                        },
                        function(data){
                            Tecnotek.hidePleaseWait();
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#catalog-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function(){
                            Tecnotek.hidePleaseWait();
                        }, true);
                }
            },
            'click .edit': function(e, value, row, index) {
                $("#panel-catalog").addClass("hidden");
                Tecnotek.UI.vars["id"] = row.id;
                $('#form-catalog').data('bootstrapValidator').resetForm(true);
                $("#username").val(row.username);
                $("#name").val(row.name);
                $("#lastname").val(row.lastname);
                $("#email").val(row.email);
                $("#cellPhone").val(row.cellPhone);
                console.debug("Is Active? " + isActive + " [" + (row.isActive) + "]");
                if(row.isActive) {
                    console.debug("Must mark as checked");
                    $("#isActive").attr("checked", "checked");
                } else {
                    console.debug("Mark isActive as unchecked");
                    $("#isActive").removeAttr("checked");
                }
                $("#panel-catalog-title").html(Tecnotek.UI.translates["edit"]);
                $("#panel-catalog").removeClass("hidden");
            }
        },
        init: function(){
            $("#btn-new").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#username").val("");
                $("#name").val("");
                $("#lastname").val("");
                $("#email").val("");
                $("#cellPhone").val("");
                $("#isActive").attr("checked", "checked");
                Tecnotek.UI.vars["id"] = 0;
                $("#panel-catalog-title").html($(this).attr("title"));
                $('#form-catalog').data('bootstrapValidator').resetForm(true);
                $("#panel-catalog").removeClass("hidden");
            });

            $("#btn-cancel").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#panel-catalog").addClass("hidden");
                $('#form-catalog').data('bootstrapValidator').resetForm(true);
            });

            $('#form-catalog').bootstrapValidator({
                excluded: ':disabled',
                message: Tecnotek.UI.translates['invalid.value'],
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                onSuccess: function(e){
                    e.preventDefault();
                    e.stopPropagation();
                    Tecnotek.showPleaseWait();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save'], {
                            id:         Tecnotek.UI.vars["id"],
                            name:       $("#name").val(),
                            lastname:   $('#lastname').val(),
                            username:   $("#username").val(),
                            cellPhone:  $("#cellPhone").val(),
                            email:      $("#email").val(),
                            isActive:   $("#isActive").is(':checked')
                        },
                        function(data){
                            Tecnotek.hidePleaseWait();
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#panel-catalog").addClass("hidden");
                                $('#form-catalog').data('bootstrapValidator').resetForm(true);
                                $("#catalog-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function(){
                            Tecnotek.hidePleaseWait();
                        }, true);
                    return false;
                },
                fields: {
                    'name': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            }
                        }
                    },
                    'lastname': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            }
                        }
                    },
                    'username': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            }
                        }
                    },
                    'email': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            }
                        }
                    },
                    'cellPhone': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            }
                        }
                    }
                }
            });
        }
    },
    Permissions: {
        init: function() {
            $('.selectpicker').selectpicker();

            $("#menuOptionsTree").jstree({
                "plugins" : [ "themes", "html_data", "checkbox", "ui" ],
                "core" : {  }
            }).bind("loaded.jstree", function (event, data) {
                // you get two params - event & data - check the core docs for a detailed description
            });

            $("#permissionsTree").jstree({
                "plugins" : [ "themes", "html_data", "checkbox", "ui" ],
                "core" : {  }
            }).bind("loaded.jstree", function (event, data) {
                // you get two params - event & data - check the core docs for a detailed description
            });

            $("#users").change(function(e){
                $("#menuOptionsTree").jstree("uncheck_all")
                $("#menuOptionsTree").jstree('close_all');
                $("#permissionsTree").jstree("uncheck_all")
                $("#permissionsTree").jstree('close_all');

                if($("#users").val() == null || $("#users").val() === "null"){
                    return;
                } else {
                    Tecnotek.showPleaseWait();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["getPrivileges"],
                        {userId: $("#users").val()},
                        function(data){
                            if(data.error === true) {
                                Tecnotek.hidePleaseWait();
                                Tecnotek.showErrorMessage(data.message);
                            } else {
                                for(i=0; i<data.menuOptions.length; i++) {
                                    $("#mo" + data.menuOptions[i]).find('.jstree-checkbox').trigger("click");
                                }
                                for(i=0; i<data.permissions.length; i++) {
                                    $("#p" + data.permissions[i]).find('.jstree-checkbox').trigger("click");
                                }
                                Tecnotek.hidePleaseWait();
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.hidePleaseWait();
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".");
                        }, true);
                }
            });

            $("#btnSave").click(function(event){
                event.preventDefault();
                if($("#users").val() == null || $("#users").val() === "null"){
                    Tecnotek.showErrorMessage("No se ha seleccionado un usuario.");
                } else {
                    var checked_ids = [];
                    $('#menuOptionsTree').jstree("get_checked",null,true).each(function(){
                        checked_ids.push(this.id.substring(2)); //The substring remove the "mo" from id
                    });

                    Tecnotek.showPleaseWait();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["savePrivileges"],
                        {   userId: $("#users").val(),
                            access: checked_ids.join(","),
                            type:   1
                        },
                        function(data){
                            Tecnotek.hidePleaseWait();
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message);
                            } else {
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.hidePleaseWait();
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".");
                        }, true);
                }
            });

            $("#btnSavePermissions").click(function(event){
                event.preventDefault();
                if($("#users").val() == null || $("#users").val() === "null"){
                    Tecnotek.showErrorMessage("No se ha seleccionado un usuario.");
                } else {
                    var checked_ids = [];
                    $('#permissionsTree').jstree("get_checked",null,true).each(function(){
                        checked_ids.push(this.id.substring(1)); //The substring remove the "p" from id
                    });

                    Tecnotek.showPleaseWait();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls["savePrivileges"],
                        {   userId: $("#users").val(),
                            access: checked_ids.join(","),
                            type:   2
                        },
                        function(data){
                            Tecnotek.hidePleaseWait();
                            if(data.error === true) {
                                Tecnotek.showErrorMessage(data.message);
                            } else {
                            }
                        },
                        function(jqXHR, textStatus){
                            Tecnotek.hidePleaseWait();
                            Tecnotek.showErrorMessage("Error getting data: " + textStatus + ".");
                        }, true);
                }
            });

            $("#users").change();
        }
    }
};