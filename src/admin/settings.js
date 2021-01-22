window.githubauthvideo_showPassword = function(elementId){
    const element = document.getElementById(elementId);
    if(!element || !element.type){
        return;
    }
    if(element.type === 'password'){
        element.type = 'text';
    } else {
        element.type = 'password';
    }
}