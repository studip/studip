jQuery(function ($) {
    let inputSel = 'form.gradebook-lecturer-weights input[type="number"]'
    const inputs = document.querySelectorAll.bind(document, inputSel)
    const adder = inputEls => [...inputEls].reduce((a, b) => a + parseInt(b.value, 10), 0)
    const percenter = (sum, item) => sum ? (parseInt(item.value, 10) / sum * 100).toFixed(1) : 0

    $(document).on('change blur', inputSel, function (event) {
        const sum = adder(inputs())
        inputs().forEach(input => {
            input.parentElement.querySelector("output").value = percenter(sum, input)
        })
    })
});
