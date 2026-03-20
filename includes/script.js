const email = document.querySelector("#email_address")
const subject = document.querySelector("#subject")
const message = document.querySelector("#message")
const subject_counter = document.querySelector('#subject_char_counter')
const message_counter = document.querySelector('#message_char_counter')
const mail = document.querySelector(".email_error")


if(email && subject && message && subject_counter && message_counter)
{
    //Subject Functionality
    subject.addEventListener("input", function()
    {
        const subject_input = subject.value.length
        subject_counter.innerText = 0 + subject.value.length
        if(subject_input >= 90)
        {
        subject_counter.classList.add("warning")
        }
        else{
            subject_counter.classList.remove("warning")
        }
    })
    //Message Functionality
    message.addEventListener("input", function(){
        const message_input = message.value.length
        message_counter.innerText = 0 + message.value.length

        if(message_input >= 290)
        {
            message_counter.classList.add("warning")
        }
        else{
            message_counter.classList.remove("warning")
        }
    })
    //Email Validation
    email.addEventListener("input", function(){
        const email_pattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/
        const mail_data = email.value
        if(mail_data === "")
        {
            mail.textContent = "Email field is empty"
        }
        else if(!email_pattern.test(mail_data))
        {
            mail.textContent = "Invalid Email Address"
        }
        else{
            mail.textContent = ""
        }
    })
}
