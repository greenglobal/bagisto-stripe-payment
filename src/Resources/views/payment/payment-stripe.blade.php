@php
    $user = null;
    $guard = $guard ?? null;
    if (!empty($guard)) {
        $user = auth()->guard($guard)->user();
    }
    $stripePayment = new GGPHP\Payment\Payment\StripePayment;
@endphp

<payment-stripe></payment-stripe>

@push('scripts')
    <script src="https://js.stripe.com/v3/"></script>
    <script type="text/x-template" id="payment-stripe-template">
        <div class="payment-checkout payment" id="payment">
            <div class="header-shipping-method">
                <h1 class="title-page">Payment Information</h1>
            </div>
            <div v-if="loading" class="content-loading">
                <img src="{{ asset('themes/velocity/assets/images/loading.gif') }}">
            </div>
            <div class="payment-methods-block" v-if="stripeInformation.last4 && !loading">
                <div>
                    <h3 class="text-default">Credit/Debit</h3>
                    <p class="description">@{{ stripeInformation.brand }} **** @{{ stripeInformation.last4 }}</p>
                </div>
            </div>
            <div class="body-stripe">
                <form class="form-stripe" :class="{ show: !(stripeInformation.last4) && !loading }">
                    <div class="row">
                        <div class="col-md-12 mb-25">
                            <label class="label-default">
                                Card Number
                            </label>
                            <div id="payment-card-number" class="input input-default empty"></div>
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="row mb-25">
                        <div class="col-md-6">
                            <label class="label-default">
                                Expiration Date
                            </label>
                            <div id="payment-card-expiry" class="input input-default empty"></div>
                            <div class="baseline"></div>
                        </div>
                        <div class="col-md-6">
                            <label class="label-default">
                                CVV
                            </label>
                            <div id="payment-card-cvv" class="input input-default empty"></div>
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="row mb-25">
                        <div class="col-md-12">
                            <label class="label-default">
                                First name
                            </label>
                            <input
                                v-model="data.first_name"
                                id="first-name"
                                data-tid="elements.form.name_placeholder"
                                class="input input-default empty"
                                type="text"
                                autocomplete="name"
                            >
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="row mb-25">
                        <div class="col-md-12">
                            <label class="label-default">
                                Last name
                            </label>
                            <input
                                v-model="data.last_name"
                                id="last-name"
                                data-tid="elements.form.name_placeholder"
                                class="input input-default empty"
                                type="text"
                                autocomplete="name"
                            >
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="row mb-25">
                        <div class="col-md-12">
                            <label class="label-default">
                                Email
                            </label>
                            <input
                                v-model="data.email"
                                id="email"
                                data-tid="elements.form.name_placeholder"
                                class="input input-default empty"
                                type="text"
                                autocomplete="name"
                                v-validate="'email'"
                                name="email"
                            />
                            <span class="control-error" v-if="errors.has('email')">@{{ errors.first('email') }}</span>
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <label class="label-default">
                                Phone
                            </label>
                            <input
                                v-model="data.phone"
                                id="phone"
                                name="phone"
                                class="input input-default empty"
                                type="text"
                                autocomplete="name"
                                v-validate="'numeric|length:10,15'"
                            />
                            <span class="control-error" v-if="errors.has('phone')">@{{ errors.first('phone') }}</span>
                            <div class="baseline"></div>
                        </div>
                    </div>
                    <div class="error error-stripe" role="alert">
                        <span class="message"></span>
                    </div>
                    <div v-if="errorApi" class="error" role="alert">
                        <span class="message"> @{{ errorApi }} </span>
                    </div>

                    <div class="mt-10 text-center">
                        <button type="submit" class="btn-stripe" data-tid="elements.form.pay_button" @click.prevent="submitForm()">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </script>
    <script>
        let stripe = Stripe('{{ $stripePayment->getPublicKey() }}'),
            elements = stripe.elements();

        Vue.component('payment-stripe', {
            template: '#payment-stripe-template',
            data() {
                return {
                    boostersName: 'payment',
                    boosters: '',
                    form: '',
                    error: '',
                    errorMessage: '',
                    formElements: '',
                    stripeInformation: {},
                    loading: true,
                    errorApi: '',
                    userId: '{{ $user->id ?? null }}',
                    guard: '{{ $guard ?? null }}',
                    data: {
                        first_name: '',
                        last_name: '',
                        email: '',
                        phone: ''
                    }
                }
            },
            mounted() {
                this.getInformationStripe(null);
                this.createFormStripe();
            },
            methods: {
                createFormStripe() {
                    var elementStyles = {
                        base: {
                            color: '#32325D',
                            fontWeight: 500,
                            fontFamily: 'Source Code Pro, Consolas, Menlo, monospace',
                            fontSize: '16px',
                            iconColor: '#666EE8',
                        },
                        invalid: {
                            color: '#E25950',

                            '::placeholder': {
                                color: '#FF8685',
                            },
                        },
                    };

                    var elementClasses = {
                        focus: 'focused',
                        empty: 'empty',
                        invalid: 'invalid',
                    };

                    var cardNumber = elements.create('cardNumber', {
                        style: elementStyles,
                        classes: elementClasses,
                        placeholder: '',
                        showIcon: true
                    });
                    cardNumber.mount('#payment-card-number');

                    var cardExpiry = elements.create('cardExpiry', {
                        style: elementStyles,
                        classes: elementClasses,
                    });
                    cardExpiry.mount('#payment-card-expiry');

                    var cardCvv = elements.create('cardCvc', {
                        style: elementStyles,
                        classes: elementClasses,
                        placeholder: 'CVV',
                    });
                    cardCvv.mount('#payment-card-cvv');

                    this.formElements = [cardNumber, cardExpiry, cardCvv];
                    this.registerElements(this.formElements);
                },
                registerElements: function(elements) {
                    this.boosters = document.querySelector('.payment');
                    this.form = this.boosters.querySelector('form');
                    this.error = this.form.querySelector('.error');
                    this.errorMessage = this.error.querySelector('.message');

                    const vm = this;
                    // Listen for errors from each Element, and show error messages in the UI.
                    var savedErrors = {};
                    elements.forEach(function(element, idx) {
                        element.on('change', function(event) {
                            if (event.error) {
                                vm.error.classList.add('visible');
                                savedErrors[idx] = event.error.message;
                                vm.errorMessage.innerText = event.error.message;
                            } else {
                                savedErrors[idx] = null;
                                // Loop over the saved errors and find the first one, if any.
                                var nextError = Object.keys(savedErrors)
                                    .sort()
                                    .reduce(function(maybeFoundError, key) {
                                        return maybeFoundError || savedErrors[key];
                                    }, null);

                                if (nextError) {
                                    // Now that they've fixed the current error, show another one.
                                    vm.errorMessage.innerText = nextError;
                                } else {
                                    // The user fixed the last error; no more errors.
                                    vm.error.classList.remove('visible');
                                }
                            }
                        });
                    });
                },
                async getInformationStripe(card = null) {
                    this.loading = true;
                    let newData = this.stripeInformation;
                    let isValid = false;

                    try {
                        if (card) {
                            isValid = true;
                            newData = card;
                        }

                    } finally {
                        this.stripeInformation = newData;
                        this.loading = false;
                    }
                },
                enableInputs: function() {
                    Array.prototype.forEach.call(
                        this.form.querySelectorAll(
                            "input[type='text'], input[type='email'], input[type='tel']"
                        ),
                        function(input) {
                            input.removeAttribute('disabled');
                        }
                    );
                },
                disableInputs: function() {
                    Array.prototype.forEach.call(
                        this.form.querySelectorAll(
                            "input[type='text'], input[type='email'], input[type='tel']"
                        ),
                        function(input) {
                            input.setAttribute('disabled', 'true');
                        }
                    );
                },
                triggerBrowserValidation: function() {
                    // The only way to trigger HTML5 form validation UI is to fake a user submit
                    // event.
                    var submit = document.createElement('input');
                    submit.type = 'submit';
                    submit.style.display = 'none';
                    this.form.appendChild(submit);
                    submit.click();
                    submit.remove();
                },
                // Listen on the form's 'submit' handler...
                async submitForm(e) {
                    // Trigger HTML5 validation UI on the form if any of the inputs fail
                    // validation.
                    var plainInputsValid = true;
                    var checkValidation = true;

                    Array.prototype.forEach.call(this.form.querySelectorAll('input'), function(input) {
                        if (input.checkValidity && !input.checkValidity()) {
                            plainInputsValid = false;
                            return;
                        }
                    });

                    if (!plainInputsValid) {
                        this.triggerBrowserValidation();
                        return;
                    }

                    // Check validation
                    await this.$validator.validateAll().then(result => {
                        if (!result) {
                            checkValidation = false;
                        }
                    });

                    if (!checkValidation) {
                        return false;
                    }

                    // Show a loading screen...
                    this.boosters.classList.add('submitting');

                    // Disable all inputs.
                    this.disableInputs();

                    // Gather additional customer data we may have collected in our form.
                    var name = this.form.querySelector('#' + this.boostersName + '-name');

                    var additionalData = {
                        name: name ? name.value : undefined,
                    };

                    // Use Stripe.js to create a token. We only need to pass in one Element
                    // from the Element group in order to create a token. We can also pass
                    // in the additional customer data we collected in our form.
                    stripe.createToken(this.formElements[0], additionalData).then(result => {
                        // Stop loading!
                        this.boosters.classList.remove('submitting');
                        if (result.token) {
                            // If we received a token, show the token ID.
                            this.boosters.classList.add('submitted');
                            this.stripeTokenHandler(result.token, this.data);
                        } else {
                            // Otherwise, un-disable inputs.
                            this.enableInputs();
                        }
                    });
                },
                stripeTokenHandler: function(token, data) {
                    let self = this;
                    self.loading = true;
                    let url = '{{ route("user.card.store-for-charge") }}';

                    self.$http.post(url, {
                        stripe_token: token.id,
                        guard: this.guard,
                        id: this.userId,
                        data: data
                    }).then(response => {
                        if (response && response.data) {
                            self.getInformationStripe(response.data.card);
                            self.errorApi = '';
                        }
                        // Replace form
                    }).catch(function (error) {
                        self.errorApi = error.response && error.response.data.message;
                        self.loading = false;
                        self.enableInputs();
                    })
                },
            }
        })
    </script>
@endpush
