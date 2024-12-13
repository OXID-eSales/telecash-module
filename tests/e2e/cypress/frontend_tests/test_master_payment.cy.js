describe('Visa Paying Test', () => {
    it('should open the home page, login, buy something and pay with Master', () => {
        cy.visit('/');

        cy.get('div.menu-dropdowns button[aria-label="Usercenter"]').click();

        cy.get('input#loginEmail').type('payment_user@oxid-esales.dev');

        cy.get('input#loginPasword').type('useruser');

        cy.get('button.btn.btn-primary').contains('Anmelden').click();
        cy.wait(1000);

        cy.get('.card-body')
            .first()
            .click()

        cy.get('#toBasket').click();


        cy.get('button.btn-minibasket[data-bs-target="#basketModal"]').first().click();

        cy.wait(500);

        cy.get('.modal-content')
            .should('be.visible')
            .find('a.btn.btn-highlight.btn-lg.w-100.mb-2')
            .click();

        cy.wait(200);

        cy.get('form#payment')
            .should('be.visible')
            .find('input[type="radio"]')
            .first()
            .check();

        cy.wait(1000);
        cy.get('button[onclick="document.querySelector(\'#payment\').requestSubmit();"]')
            .click();

        cy.wait(1000);

        cy.get('.sticky-md-top button.btn.btn-highlight.btn-lg.w-100')
            .click({ force: true });

        cy.get('#brandTypeSelect + .select2')
            .click();

        // Then click on the VISA option
        cy.get('.select2-results__option:contains("Master")')
            .click();

        // Now continue with filling in the card details
        cy.get('#cardNumber')
            .should('be.visible')
            .type('5404 1070 0002 0010');

        cy.get('#expiryMonth')
            .select('12');

        cy.get('#expiryYear')
            .select('2030');

        cy.get('#cardCode_masked')
            .type('999');

        cy.wait(3000);

        cy.get('#buttonRow #nextBtn')
            .should('be.visible')
            .click();

        cy.wait(1000);

        cy.get('Thank you');
    });
});
