var Tecnotek = Tecnotek || {};

Tecnotek.Patients = {
    Graduate: {
        List: {
            operateFormatter: function (value, row, index) {
                return [
                    '<a class="edit" href="' + Tecnotek.UI.urls['edit-patient'].replace("xxx", row.id) + '" title="Editar">',
                    '<i class="glyphicon glyphicon-edit"></i>',
                    '</a>',
                    '<a class="graduate" href="#" title="Readmitir">',
                    '<i class="glyphicon glyphicon-upload"></i>',
                    '</a>'
                ].join('');
            },
            operateEvents: {
                'click .like': function (e, value, row, index) {
                    alert('You click like action, row: ' + JSON.stringify(row));
                },
                'click .graduate': function (e, value, row, index) {
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-graduate'])) {
                        Tecnotek.showPleaseWait();
                        var url = Tecnotek.UI.urls['graduate-patient'];
                        url = url.replace('xid', row.id);
                        Tecnotek.ajaxCall(url, {
                            },
                            function (data) {
                                Tecnotek.hidePleaseWait();
                                if (data.error) {
                                    Tecnotek.showErrorMessage(data.msg);
                                } else {
                                    $("#patients-list").bootstrapTable('refresh');
                                    Tecnotek.showInfoMessage(data.msg);
                                }
                            }, function () {
                                Tecnotek.hidePleaseWait();
                            }, true);
                    }
                },
                'click .delete': function (e, value, row, index) {
                    if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-delete'])) {
                        Tecnotek.ajaxCall(Tecnotek.UI.urls['delete-sport'], {
                                id: row.id
                            },
                            function (data) {
                                if (data.error) {
                                    Tecnotek.showErrorMessage(data.msg);
                                } else {
                                    $("#sports-list").bootstrapTable('refresh');
                                    Tecnotek.showInfoMessage(data.msg);
                                }
                            }, function () {
                            }, true);
                    }
                },
                'click .edit': function (e, value, row, index) {

                }
            },
            init: function () {
                $("#btn-new").click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $("#name").val("");
                    Tecnotek.UI.vars["sport_id"] = 0;
                    $("#panel-sport-title").html($(this).attr("title"));
                    $('#form-sport').data('bootstrapValidator').resetForm(true);
                    $("#panel-sport").removeClass("hidden");
                });

                $("#btn-cancel").click(function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    $("#panel-sport").addClass("hidden");
                    $('#form-sport').data('bootstrapValidator').resetForm(true);
                });

                $('#form-sport').bootstrapValidator({
                    excluded: ':disabled',
                    message: Tecnotek.UI.translates['invalid.value'],
                    feedbackIcons: {
                        valid: 'glyphicon glyphicon-ok',
                        invalid: 'glyphicon glyphicon-remove',
                        validating: 'glyphicon glyphicon-refresh'
                    },
                    onSuccess: function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        var $name = $("#name").val();
                        Tecnotek.ajaxCall(Tecnotek.UI.urls['save-sport'], {
                                id: Tecnotek.UI.vars["sport_id"],
                                name: $name
                            },
                            function (data) {
                                if (data.error) {
                                    Tecnotek.showErrorMessage(data.msg);
                                } else {
                                    $("#panel-sport").addClass("hidden");
                                    $('#form-sport').data('bootstrapValidator').resetForm(true);
                                    $("#sports-list").bootstrapTable('refresh');
                                    Tecnotek.showInfoMessage(data.msg);
                                }
                            }, function () {
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
                        }
                    }
                });
            }
        }
    },
    Edit: {
        amountFormatter: function (value) {
            return '<div  style="color:#4f94c6">' +
                value + '</div>';
        },
        updateActivitiesForm: function () {
            Tecnotek.showPleaseWait();
            var url = Tecnotek.UI.urls['get-activity-form'];
            url = url.replace('xid', $("#activityType").val());
            $( "#activityContainer" ).load( url, {},
                function (responseText, textStatus, XMLHttpRequest) {
                    if (textStatus == "error") {
                        Tecnotek.hidePleaseWait();
                        Tecnotek.showErrorMessage("Ocurrió un error inesperado, por favor intenta otra vez");
                    }
                } );
        },
        initActivitiesEvents: function() {
            $(".activity").click(function(e){
                e.preventDefault();
                Tecnotek.Patients.Edit.loadActivityItems($(this));
            });
            $(".activity-li.active>a").click();
            Tecnotek.hidePleaseWait();
        },
        initItemsEvents: function(){
            $("select.item-element").change(function(e){
                var itemId = $(this).attr('item-id');
                var value = $(this).val();
                Tecnotek.Patients.Edit.savePatientItem(itemId, value);
            });
            $("textarea.item-element").blur(function(e){
                var itemId = $(this).attr('item-id');
                var value = $(this).val();
                Tecnotek.Patients.Edit.savePatientItem(itemId, value);
            });
            $("input:checkbox.item-element").change(function(e){
                var itemId = $(this).attr('item-id');
                var value = "";
                $('input:checkbox.item-element').each(function () {
                    if(itemId == $(this).attr("item-id")) {
                        value += (this.checked ? "["+$(this).val()+"]" : "");
                    }
                });
                console.debug("ItemId: " + itemId + ", value: " + value);
                Tecnotek.Patients.Edit.savePatientItem(itemId, value);
            });
            $(".timeinput").blur(function(e){
                var itemId = $(this).attr('item-id');
                var value = $(this).val();
                Tecnotek.Patients.Edit.savePatientItem(itemId, value);
            });
            $('.timepicker').datetimepicker({
                format: 'LT'
            });
            Tecnotek.hidePleaseWait();
        },
        savePatientItem: function(itemId, value){
            Tecnotek.ajaxCall(Tecnotek.UI.urls['save-patient-item-value'], {
                    patientId:  Tecnotek.UI.vars["patient-id"],
                    itemId:     itemId,
                    value:      value
                },
                function (data) {
                    if (data.error) {
                        Tecnotek.showErrorMessage(data.msg);
                    }
                }, function () {
                }, true);
        },
        loadActivityItems: function ($this) {
            var id = $this.attr("rel");
            $("#activity-name-title").html($this.html());
            $( "#itemsContainer").html("");
            var url = Tecnotek.UI.urls['get-activity-items'];
            url = url.replace('xid', id);
            Tecnotek.showPleaseWait();
            $( "#itemsContainer" ).load( url, {},
                function (responseText, textStatus, XMLHttpRequest) {
                    if (textStatus == "error") {
                        Tecnotek.hidePleaseWait();
                        Tecnotek.showErrorMessage("Ocurrió un error inesperado, por favor intenta otra vez");
                    }
                } );
            $(".activity-li").removeClass("active");
            $this.parent('li').addClass('active');
        },
        otherPentionId: 1,
        showOtherInput: function () {
            $("#otherInput").val("");
            if ($("#pention").val() == Tecnotek.Patients.Edit.otherPentionId) {
                $("#otherInputContainer").show();
            } else {
                $("#otherInputContainer").hide();
            }
        },
        init: function () {
            Tecnotek.Patients.Edit.showOtherInput();

            $("#activityType").change(function(e){
                Tecnotek.Patients.Edit.updateActivitiesForm();
            });

            $("#btn-add-pention").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                $("#amount").val("");
                Tecnotek.UI.vars["association-id"] = 0;
                $("#panel-add-pention-title").html($(this).attr("title"));
                $('#form-add-pention').data('bootstrapValidator').resetForm(true);
                $("#panel-add-pention").removeClass("hidden");
            });

            $("#btn-pention-cancel").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                $("#panel-add-pention").addClass("hidden");
                $('#form-add-pention').data('bootstrapValidator').resetForm(true);
            });

            $("#pention").change(function (e) {
                e.preventDefault();
                e.stopPropagation();
                Tecnotek.Patients.Edit.showOtherInput();
            });

            $('#form-add-pention').bootstrapValidator({
                excluded: ':disabled',
                message: Tecnotek.UI.translates['invalid.value'],
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                onSuccess: function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save-patient-association'], {
                            id: Tecnotek.UI.vars["patient-id"],
                            association: 'pention',
                            associationId: Tecnotek.UI.vars["association-id"],
                            action: 'save',
                            pentionId: $("#pention").val(),
                            name: $("#otherInput").val(),
                            amount: $("#amount").val()
                        },
                        function (data) {
                            if (data.error) {
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#panel-add-pention").addClass("hidden");
                                $('#form-add-pention').data('bootstrapValidator').resetForm(true);
                                if (Tecnotek.UI.vars["association-id"] == 0) {
                                    //It's inserting
                                    $('#pentions-list').bootstrapTable('insertRow', {
                                        index: 0,
                                        row: {
                                            id: data.id,
                                            pentionId: $("#pention").val(),
                                            name: data.name,
                                            amount: data.amount
                                        }
                                    });
                                } else {
                                    $('#pentions-list').bootstrapTable('updateRow', {
                                        index: Tecnotek.UI.vars["pention-index"],
                                        row: {
                                            id: data.id,
                                            pentionId: $("#pention").val(),
                                            name: data.name,
                                            amount: data.amount
                                        }
                                    });
                                }
                                $("#pentions-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function () {
                        }, true);
                    return false;
                },
                fields: {
                    'amount': {
                        validators: {
                            notEmpty: {
                                message: Tecnotek.UI.translates['field.not.empty']
                            },
                            integer: {
                                message: Tecnotek.UI.translates['validation.only.digits']
                            }
                        }
                    }
                }
            });

            Tecnotek.Patients.Edit.updateActivitiesForm();
        },
        operateFormatter: function (value, row, index) {
            return [
                '<a class="edit-pention" href="#" title="Editar">',
                '<i class="glyphicon glyphicon-edit"></i>',
                '</a>',
                '<a class="delete-pention" href="#" title="Eliminar">',
                '<i class="glyphicon glyphicon-remove"></i>',
                '</a>'
            ].join('');
        },
        operateEvents: {
            'click .delete-pention': function (e, value, row, index) {
                if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-pention-delete'])) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save-patient-association'], {
                            id: Tecnotek.UI.vars["patient-id"],
                            association: 'pention',
                            associationId: row.id,
                            action: 'delete'
                        },
                        function (data) {
                            if (data.error) {
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#pentions-list").bootstrapTable('remove', {
                                    field: 'id',
                                    values: row.id
                                });
                                $("#pentions-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(Tecnotek.UI.translates['pention-remove-success']);
                            }
                        }, function () {
                        }, true);
                }
            },
            'click .edit-pention': function (e, value, row, index) {
                Tecnotek.UI.vars["pention-index"] = index;
                $("#panel-add-pention").addClass("hidden");
                Tecnotek.UI.vars["association-id"] = row.id;
                $('#form-add-pention').data('bootstrapValidator').resetForm(true);
                $("#amount").val(row.amount);
                $("#pention").val(row.pentionId);
                $("#panel-add-pention-title").html(Tecnotek.UI.translates["edit-pention"]);
                Tecnotek.Patients.Edit.showOtherInput();
                $("#otherInput").val(row.name);
                $("#panel-add-pention").removeClass("hidden");
            }
        }
    },
    List: {
        operateFormatter: function (value, row, index) {
            if(Tecnotek.UI.vars['can-edit']){ //Can edit the patients
                return [
                    '<a class="edit" href="' + Tecnotek.UI.urls['edit-patient'].replace("xxx", row.id) + '" title="Editar">',
                    '<i class="glyphicon glyphicon-edit"></i>',
                    '</a>',
                    '<a class="graduate" href="#" title="Egresar">',
                    '<i class="glyphicon glyphicon-download"></i>',
                    '</a>'
                ].join('');
            } else { //Cannot edit the patients
                return '';
            }
        },
        operateEvents: {
            'click .like': function (e, value, row, index) {
                alert('You click like action, row: ' + JSON.stringify(row));
            },
            'click .graduate': function (e, value, row, index) {
                if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-graduate'])) {
                    Tecnotek.showPleaseWait();
                    var url = Tecnotek.UI.urls['graduate-patient'];
                    url = url.replace('xid', row.id);
                    Tecnotek.ajaxCall(url, {
                        },
                        function (data) {
                            Tecnotek.hidePleaseWait();
                            if (data.error) {
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#patients-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function () {
                            Tecnotek.hidePleaseWait();
                        }, true);
                }
            },
            'click .delete': function (e, value, row, index) {
                if (Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-delete'])) {
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['delete-sport'], {
                            id: row.id
                        },
                        function (data) {
                            if (data.error) {
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#sports-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function () {
                        }, true);
                }
            },
            'click .edit': function (e, value, row, index) {

            }
        },
        init: function () {
            $("#btn-new").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                $("#name").val("");
                Tecnotek.UI.vars["sport_id"] = 0;
                $("#panel-sport-title").html($(this).attr("title"));
                $('#form-sport').data('bootstrapValidator').resetForm(true);
                $("#panel-sport").removeClass("hidden");
            });

            $("#btn-cancel").click(function (e) {
                e.preventDefault();
                e.stopPropagation();
                $("#panel-sport").addClass("hidden");
                $('#form-sport').data('bootstrapValidator').resetForm(true);
            });

            $('#form-sport').bootstrapValidator({
                excluded: ':disabled',
                message: Tecnotek.UI.translates['invalid.value'],
                feedbackIcons: {
                    valid: 'glyphicon glyphicon-ok',
                    invalid: 'glyphicon glyphicon-remove',
                    validating: 'glyphicon glyphicon-refresh'
                },
                onSuccess: function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    var $name = $("#name").val();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save-sport'], {
                            id: Tecnotek.UI.vars["sport_id"],
                            name: $name
                        },
                        function (data) {
                            if (data.error) {
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#panel-sport").addClass("hidden");
                                $('#form-sport').data('bootstrapValidator').resetForm(true);
                                $("#sports-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function () {
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
                    }
                }
            });
        }
    }
};