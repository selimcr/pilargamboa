var Tecnotek = Tecnotek || {};

Tecnotek.PatientsPentions = {
    List: {
        amountFormatter: function(value){
            return '<div  style="color:#4f94c6">' +
                value + '</div>';
        },
        otherPentionId: 1,
        showOtherInput: function(){
            $("#otherInput").val("");
            if($("#pention").val() == Tecnotek.PatientsPentions.List.otherPentionId){
                $("#otherInputContainer").show();
            } else {
                $("#otherInputContainer").hide();
            }
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
            'click .delete': function (e, value, row, index) {
                if(Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-delete'])){
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['delete-patients-pentions'], {
                            id:     row.id
                        },
                        function(data){
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#entities-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function(){
                        }, true);
                }
            },
            'click .edit': function(e, value, row, index) {
                $("#panel-entity").addClass("hidden");
                Tecnotek.UI.vars["entity_id"] = row.id;
                $('#form-entity').data('bootstrapValidator').resetForm(true);
                $("#amount").val(row.amount);
                $("#pention").val(row.pentionId);
                $("#patient").val(row.patientId);
                $("#panel-entity-title").html(Tecnotek.UI.translates["edit-patients-pentions"]);
                Tecnotek.PatientsPentions.List.showOtherInput();
                $("#otherInput").val(row.pention);
                $("#panel-entity").removeClass("hidden");
            }
        },
        init: function(){
            $("#pention").change(function(e){
                e.preventDefault();
                e.stopPropagation();
                Tecnotek.PatientsPentions.List.showOtherInput();
            });

            $("#btn-new").click(function(e){
                Tecnotek.PatientsPentions.List.showOtherInput();
                e.preventDefault();
                e.stopPropagation();
                $("#name").val("");
                Tecnotek.UI.vars["entity_id"] = 0;
                $("#panel-entity-title").html($(this).attr("title"));
                $('#form-entity').data('bootstrapValidator').resetForm(true);
                $("#panel-entity").removeClass("hidden");
            });

            $("#btn-cancel").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#panel-entity").addClass("hidden");
                $('#form-entity').data('bootstrapValidator').resetForm(true);
            });

            $('#form-entity').bootstrapValidator({
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
                    console.debug("Form validation success");
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save-entity'], {
                            id:         Tecnotek.UI.vars["entity_id"],
                            patientId:  $("#patient").val(),
                            pentionId:  $("#pention").val(),
                            amount:     $("#amount").val(),
                            detail:     $("#otherInput").val()
                        },
                        function(data){
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#panel-entity").addClass("hidden");
                                $('#form-entity').data('bootstrapValidator').resetForm(true);
                                $("#entities-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function(){
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