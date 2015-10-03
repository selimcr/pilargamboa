var Tecnotek = Tecnotek || {};

Tecnotek.Results = {
    loadItemsResults: function() {



        $('.yes-no-item-results').each(function () {
            var url = Tecnotek.UI.urls['render-yes-no-results'];
            url = url.replace('xid', $(this).attr('item-id'));
            var useActivityTitle = $(this).attr('use-activity-title')? $(this).attr('use-activity-title'):0;
            $(this).load( url, {useActivityTitle: useActivityTitle} );
        });

        $('.yes-no-plus-entity-item-results').each(function () {
            var url = Tecnotek.UI.urls['render-yes-no-plus-entity-results'];
            url = url.replace('xid', $(this).attr('item-id'));
            $(this).load( url );
        });

        $('.constants-list-item-results').each(function () {
            var url = Tecnotek.UI.urls['render-constants-select-results'];
            url = url.replace('xid', $(this).attr('item-id'));
            var useActivityTitle = $(this).attr('use-activity-title')? $(this).attr('use-activity-title'):0;
            $(this).load( url, {useActivityTitle: useActivityTitle} );
        });

        $('.entity-item-results').each(function () {
            var url = Tecnotek.UI.urls['render-entity-results'];
            url = url.replace('xid', $(this).attr('item-id'));
            var useActivityTitle = $(this).attr('use-activity-title')? $(this).attr('use-activity-title'):0;
            $(this).load( url, {useActivityTitle: useActivityTitle} );
        });
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
            $( "#activityContainer" ).load( url );
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
                    value += (this.checked ? "["+$(this).val()+"]" : "");
                });
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
            $( "#itemsContainer" ).load( url );
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

    }
};