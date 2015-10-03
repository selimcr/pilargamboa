var Tecnotek = Tecnotek || {};

Tecnotek.Profile = {
    init: function () {
        $("#btn-new").click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            $("#name").val("");
            Tecnotek.UI.vars["id"] = 0;
            $("#panel-catalog-title").html($(this).attr("title"));
            $('#form-catalog').data('bootstrapValidator').resetForm(true);
            $("#panel-catalog").removeClass("hidden");
        });

        $("#btn-cancel").click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            $("#panel-catalog").addClass("hidden");
            $('#form-catalog').data('bootstrapValidator').resetForm(true);
        });

        $('#form-info').bootstrapValidator({
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
                Tecnotek.Profile.updateInfo();
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

        $('#form-password').bootstrapValidator({
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
                Tecnotek.Profile.updatePassword();
                return false;
            },
            fields: {
                'current': {
                    validators: {
                        notEmpty: {
                            message: Tecnotek.UI.translates['field.not.empty']
                        }
                    }
                },
                'new': {
                    validators: {
                        notEmpty: {
                            message: Tecnotek.UI.translates['field.not.empty']
                        },
                        identical: {
                            field: 'confirm',
                            message: Tecnotek.UI.translates['password.confirm.not.same']
                        },
                        stringLength: {
                            message: Tecnotek.UI.translates['password.lenght.error'],
                            max: 8,
                            min: 6
                        }
                    }
                },
                'confirm': {
                    validators: {
                        identical: {
                            field: 'new',
                            message: Tecnotek.UI.translates['password.confirm.not.same']
                        }
                    }
                }
            }
        });
    },
    updateInfo: function () {
        var $name = $("#name").val();
        var $lastname = $("#lastname").val();
        var $email = $("#email").val();
        var $cellPhone = $("#cellPhone").val();
        Tecnotek.showPleaseWait();
        Tecnotek.ajaxCall(Tecnotek.UI.urls['update-account'], {
                id: Tecnotek.UI.vars["id"],
                name: $name,
                lastname: $lastname,
                cellPhone: $cellPhone,
                email: $email
            },
            function (data) {
                Tecnotek.hidePleaseWait();
                if (data.error) {
                    Tecnotek.showErrorMessage(data.msg);
                } else {
                    $("#account-name").html($name + " " + $lastname);
                    $("#account-email").html($email);
                    $("#account-phone").html($cellPhone);
                    $('#form-info').data('bootstrapValidator').resetForm(true);
                    $("#name").val($name);
                    $("#lastname").val($lastname);
                    $("#email").val($email);
                    $("#cellPhone").val($cellPhone);
                    Tecnotek.showInfoMessage(data.msg);
                }
            }, function () {
                Tecnotek.hidePleaseWait();
            }, true);
    },
    updatePassword: function () {
        var $current = $("#current").val();
        var $new = $("#new").val();
        Tecnotek.showPleaseWait();
        Tecnotek.ajaxCall(Tecnotek.UI.urls['update-password'], {
                id: Tecnotek.UI.vars["id"],
                current: $current,
                new: $new
            },
            function (data) {
                Tecnotek.hidePleaseWait();
                if (data.error) {
                    Tecnotek.showErrorMessage(data.msg);
                } else {
                    $('#form-password').data('bootstrapValidator').resetForm(true);
                    Tecnotek.showInfoMessage(data.msg);
                }
            }, function () {
                Tecnotek.hidePleaseWait();
            }, true);
    }
};