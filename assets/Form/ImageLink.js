document.addEventListener('DOMContentLoaded', () => {
    // Your code here


console.log('ImageLink.js loaded');

console.log('test before create');

const createEdit = document.querySelector('#createEdit');


var edit = false;
    if (createEdit) {
        // if inner html of createEdit = 'Edit your trick' then print edit
        if (createEdit.innerHTML === 'Edit trick' || createEdit.innerHTML === 'Modifier la figure') {
            console.log('edit');
            edit = true;
        } else {
            console.log('create');

        }
        console.log('test after create');
    }
// create a new link
const addImageLinkLink = document.createElement('a')
// add a css class to the link
addImageLinkLink.classList.add('add_imagelink_list')
// add set a fake href to the link
addImageLinkLink.href='#'
// add a text to the link
addImageLinkLink.innerText="Ajouter un lien d'image"
// adds a data attribute to the link
addImageLinkLink.dataset.collectionHolderClass='imagelinks'

// create a new list item and append the link to it
const newLinkLi = document.createElement('li').append(addImageLinkLink)

// get the ul element
const collectionHolder = document.querySelector('ul.imagelinks')

if(collectionHolder) {
// append the new list item to the ul element
collectionHolder.appendChild(addImageLinkLink);
}

// add a click event listener to the link, the function will be called when the link is clicked, it takes an event as a parameter
const addImageFormToCollectionEdit = (e) => {
    e.preventDefault();
    const collectionHolderClass = e.currentTarget.dataset.collectionHolderClass;
    const collectionHolder = document.querySelector(`.${collectionHolderClass}`);
    const parentElement = collectionHolder.parentElement;

    // Count existing elements with a specific pattern in their id or for attributes
    const existingElements = parentElement.querySelectorAll(`input[id^='trick_imagelinks_']`);
    let maxIndex = -1;
    existingElements.forEach((inputElement) => {
        const idMatch = inputElement.id.match(/(?<=trick_imagelinks_)\d+/);
        if (idMatch) {
            const index = parseInt(idMatch[0], 10);
            if (index > maxIndex) {
                maxIndex = index;
            }
        }
    });
    const startIndex = maxIndex + 1;
    console.log('startIndex', startIndex)

    const item = document.createElement('li');
    item.innerHTML = collectionHolder
        .dataset
        .prototype
        .replace(
            /__name__/g,
            startIndex
        )
        // Fix the 'for' attribute of the label
        .replace(
            /for="trick_imagelinks___name___content"/g,
            `for="trick_imagelinks_${startIndex}_content"`
        );

    collectionHolder.appendChild(item);
    collectionHolder.dataset.index = startIndex + 1;

    const removelinkbutton = document.createElement('button');
    removelinkbutton.innerHTML = "Retirer le lien";
    e.currentTarget.parentNode.appendChild(removelinkbutton);

    removelinkbutton.addEventListener("click", removeImageFormFromCollection)
}

const addImageFormToCollectionCreate = (e) => {
    e.preventDefault();
    // finds the closest parent element with the class name specified in the data attribute, namely 'imagelinks'
	const collectionHolder = document.querySelector('.' + e.currentTarget.dataset.collectionHolderClass);

    // create a new list item
    const item = document.createElement('li');


    
    // sets the innerHTML of the list item to the prototype of the collection
    item.innerHTML = collectionHolder
        // access the prototype attribute of the collection
        .dataset
        .prototype
        // replace the __name__ placeholder with the current index of the collection
        .replace(
            /__name__/g,
            collectionHolder.dataset.index
        );

    // append the new list item to the ul element
    collectionHolder.appendChild(item);

    // increment the index of the collection so that the next item will have a different index
    collectionHolder.dataset.index++;



    // console.log('add link');

    // create a button below the link with an id of coucou
    const removelinkbutton = document.createElement('button');
    // add the class btn btn-danger to the button
    removelinkbutton.classList.add('btn', 'btn-danger', 'mt-2');
    removelinkbutton.innerHTML = "Retirer le lien";
    // add the button as a sibling of the link
    e.currentTarget.parentNode.appendChild(removelinkbutton);

    // add a click event listener to the button
    removelinkbutton.addEventListener("click", removeImageFormFromCollection)
    // if the button is clicked, the removeImageFormFromCollection function will be called

    // console.log('end test link');
}



function removeImageFormFromCollection(e) {
    // get the previous sibling of the button

    const sibling = e.currentTarget.previousSibling;
    // remove the parent element from the DOM
    sibling.remove();

    // also remove the button itself
    e.currentTarget.remove();
}

if (edit === true) {
    addImageLinkLink.addEventListener("click", addImageFormToCollectionEdit)
} else {
    addImageLinkLink.addEventListener("click", addImageFormToCollectionCreate)
}
// add the click event listener to the link

// console.log(addImageLinkLink);

});