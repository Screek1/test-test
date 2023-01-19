jQuery(function ($) {
  const $_ = {
    init() {
      this.initCache();
      this.initValidation();
      this.initResetButton();
      this.initClearInput();
      this.initEditField();
      this.initCancelEdit();
      this.initSubmitOnChangeForm();
      this.initValidationCallback();
    },

    initCache() {
      this.$form = $('.js-ajax-form');
      this.$resetButton = $('.js-form-reset-button');
      this.$clearInput = $('.js-clear-input');
      this.$editField = $('.js-edit-field');
      this.$editFieldButton = $('.js-edit-field-button');
      this.$cancelEdit = $('.js-cancel-edit');
      this.$submitOnChange = $('.js-submit-on-change');
      this.$dropdownButton = $('.js-dropdown-button');
      this.$keywordsList = $('.js-keywords-list');
      console.log(this.$form)

      this.options = {
        'type': 'POST',
        'handler': '',
        'contentType': 'application/json',
        'processData': false,
        'onload': false,
        'success': false,
        'error': false,
      };

      this.animationEvents = 'animationend webkitAnimationEnd oAnimationEnd MSAnimationEnd';
    },

    initSubmitOnChangeForm() {
      $_.$submitOnChange.on('change', (e) => {
        e.currentTarget.submit();
      });
    },

    initEditField() {
      $_.$editFieldButton.on('click', (e) => {
        const
          $currentTarget = $(e.currentTarget),
          $wrap = $currentTarget.closest($_.$editField),
          $relatedInput = $wrap.find('input');

        $wrap.toggleClass('_active');
        if ($relatedInput.length) $relatedInput.focus();
      });
    },

    initCancelEdit() {
      $_.$cancelEdit.each((key, item) => {
        const
          $currentButton = $(item),
          $parentForm = $currentButton.closest('form'),
          $relatedEditField = $parentForm.find($_.$editField);

        let initialData = $parentForm.serializeArray();

        $currentButton.on('click', () => {
          $relatedEditField.removeClass('_active');

          initialData.forEach(({name, value}) => {
            $parentForm.find(`[name="${name}"]`).val(value).trigger('change').valid();
          });
        });

        $parentForm.on('trigger:update-data', () => {
          initialData = $parentForm.serializeArray();
        });
      })
    },

    initClearInput() {
      $_.$clearInput.on('click', (e) => {
        const
          $currentInput = $(e.currentTarget),
          dataClearInput = $currentInput.data('clear-input-name'),
          $closestForm = $currentInput.closest('form'),
          $inputToClear = $closestForm.find(`input[name="${dataClearInput}"]`),
          isAjaxForm = $closestForm.is($_.$form);

        $inputToClear.val('').trigger('change');
        if (isAjaxForm) $inputToClear.valid();
      })
    },

    initResetButton() {
      $_.$resetButton.on('click', (e) => {
        const
          $currentTarget = $(e.currentTarget),
          dataException = $currentTarget.data('exception'),
          $relatedForm = $currentTarget.closest('form'),
          $formItems = $relatedForm.find('input, select').filter((key, item) => {
            if (dataException.indexOf(item.name) === -1) return item;
          }),
          $textInputs = $formItems.filter('input[type="text"]'),
          $radioInputs = $formItems.filter('input[type="radio"]'),
          $checkedInputs = $formItems.filter('input[checked]'),
          $selects = $formItems.filter('select'),
          $relatedSelectModules = $selects.closest('.js-select-module');

        $currentTarget.trigger('trigger:reset:start');

        $relatedSelectModules.each((key, item) => {
          $(item).find('.js-select-module-option').eq(0).click();
        });

        $textInputs.attr('value', '');
        $checkedInputs.removeAttr('checked');
        $radioInputs.filter('[value=""]').attr('checked', true);
        $selects.find('option').removeAttr('selected');

        $relatedForm.trigger('reset');
        $relatedForm.find($_.$dropdownButton).trigger('trigger:reset');
        $relatedForm.find($_.$keywordsList).html('');
        $currentTarget.trigger('trigger:reset:complete');
      });
    },

    initValidation() {
      $.validator.addMethod('validatePhoneCode', (value, element) => {
        return element._validPhoneCode ? element._validPhoneCode(value) : false;
      });

      $.validator.addClassRules({
        usermail: {
          email: true
        },
        required: {
          required: true,
        },
        phonecode: {
          validatePhoneCode: true,
        },
        password: {
          minlength: 6
        },
        passwordconfirm: {
          minlength: 6,
          equalTo: '.js-input-new-password'
        }
      });
    },

    initValidationCallback() {
      this.$form.each((key, item) => {
        $(item).validate({
          errorPlacement: (error) => {
            error.remove();
          },
          submitHandler: (form) => {
            $_.$form_cur = $(form);
            $_._sendForm(form);
          }
        })
      });
    },

    _sendForm(form) {
      const
        {type, handler, contentType, processData} = $_.options,
        $form = $(form),
        action = $form.attr('action'),
        method = $form.attr('method'),
        googleAction = $form.data('google-action'),
        category = $form.data('category'),
        label = $form.data('label'),
        disableBeforeSend = $form.data('disable-before-send'),
        formData = _getJsonFormData(form);

      if (disableBeforeSend) $form.addClass('_disable');

      // if (googleAction && category && label) {
      //   gtag('event', googleAction, {
      //     'event_category': category,
      //     'event_label': label,
      //     'non_interaction': true
      //   });
      // }

      $.ajax({
        type: method || type,
        url: action || handler,
        contentType: contentType,
        processData: processData,
        dataType: 'json',
        data: formData,
      })
        .done(() => {
          $_._successHandler();
        })
        .fail((error) => {
          $_._errorHandler(error);
          $form.removeClass('_disable')
        })
        .always(() => {
          $_.$form_cur = false;
        });
    },

    _successHandler() {
      const
        $form = $($_.$form_cur),
        $parentScroll = $form.closest('.js-smooth-scroll'),
        $input = $form.find('input'),
        $tyMessage = $form.find('.js-ty-message'),
        reload = $form.data('reload-page'),
        preventClear = $form.data('prevent-clear'),
        removeTy = $form.data('remove-ty'),
        successTrigger = $form.data('success-trigger'),
        extraRequest = sessionStorage.getItem('extra_request');

      $form.addClass('_ty');

      if (removeTy) {
        if ($tyMessage.length) {
          $tyMessage.on($_.animationEvents, () => {
            $form.removeClass('_ty');
          });
        } else {
          setTimeout(() => {
            $form.removeClass('_ty');
          }, 2000);
        }
      }

      if (!preventClear) {
        setTimeout(() => {
          $form.trigger('reset').find('._active, .valid, .error').removeClass('_active valid error');
          $input.removeClass('_active');
        }, 500);
      }

      if ($parentScroll.length) {
        $parentScroll.trigger('trigger:update-scroll');
      }

      if (successTrigger) {
        const {target, event} = successTrigger;

        if (target && event) {
          $(target).trigger(event);
        } else {
          $form.trigger(successTrigger);
        }
      }

      if (extraRequest) {
        sessionStorage.removeItem('extra_request');
        _sendAjax(_safeParseJson(extraRequest));
      } else if (reload) {
        window.location.reload();
      }
    },

    _errorHandler(response) {
      const
        $form = $($_.$form_cur),
        $errorMessage = $form.find('.alert-danger'),
        {responseJSON = {}} = response,
        {error, errors} = responseJSON;

      if ($errorMessage) {
        $errorMessage.text(error || errors.join('\n'))

        setTimeout(() => {
          $errorMessage.text('')
        }, 5000)
      }
    }
  }
  $(document).ready(() => {
    $_.init();
  });
});
