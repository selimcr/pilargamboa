var Tecnotek = Tecnotek || {};

Tecnotek.Catalog = {
    List: {
        catalogTableParams: function() {
            return {
                entity: Tecnotek.UI.vars["entity"]
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
            'click .like': function (e, value, row, index) {
                alert('You click like action, row: ' + JSON.stringify(row));
            },
            'click .delete': function (e, value, row, index) {
                if(Tecnotek.showConfirmationQuestion(Tecnotek.UI.translates['confirm-delete'])){
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['delete'], {
                            id:     row.id,
                            entity: Tecnotek.UI.vars["entity"]
                        },
                        function(data){
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#catalog-list").bootstrapTable('refresh');
                                Tecnotek.showInfoMessage(data.msg);
                            }
                        }, function(){
                        }, true);
                }
            },
            'click .edit': function(e, value, row, index) {
                $("#panel-catalog").addClass("hidden");
                Tecnotek.UI.vars["id"] = row.id;
                $('#form-catalog').data('bootstrapValidator').resetForm(true);
                $("#name").val(row.name);
                $("#panel-catalog-title").html(Tecnotek.UI.translates["edit"]);
                $("#panel-catalog").removeClass("hidden");
            }
        },
        init: function(){
            $("#btn-new").click(function(e){
                e.preventDefault();
                e.stopPropagation();
                $("#name").val("");
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
                    var $name = $("#name").val();
                    Tecnotek.ajaxCall(Tecnotek.UI.urls['save'], {
                            id:     Tecnotek.UI.vars["id"],
                            name:   $name,
                            entity: Tecnotek.UI.vars["entity"]
                        },
                        function(data){
                            if(data.error){
                                Tecnotek.showErrorMessage(data.msg);
                            } else {
                                $("#panel-catalog").addClass("hidden");
                                $('#form-catalog').data('bootstrapValidator').resetForm(true);
                                $("#catalog-list").bootstrapTable('refresh');
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