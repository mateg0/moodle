require(['core/first', 'jquery', 'core/ajax'],
    function (core, $, ajax) {
        const showCourseAbout = async (event) => {
            const registerEventListeners = () => {
                const closeCourseAbout = (event) => {
                    if(!event.target.closest('div.course-about-wrapper')) {
                        document.querySelector('div.course-about-wrapper').remove();
                        document.querySelector('div.hide-ui').remove();

                        document.removeEventListener('click', closeCourseAbout);
                    }
                };

                const redirectToCourse = () => {
                    window.location.href = courseLink;
                };

                document.addEventListener('click', closeCourseAbout);
                courseAboutWrapper
                    .querySelector('div.course-about-participate button')
                    ?.addEventListener('click', redirectToCourse);
            };

            let courseData;
            let aboutCourse;

            const eventTarget = event.target;
            const courseBody = eventTarget.closest('.course-promo-body');
            const courseCard = eventTarget.closest('.course-promo');
            const courseImage = courseCard.querySelector('.course-image img');
            const courseLink = courseCard.querySelector('.course-title a').href;

            const courseId = courseBody.dataset.course;
            const courseTeacher = courseBody.querySelector('.course-teacher span')?.textContent?.trim();

            const courseAboutWrapper = document.createElement('div');
            const hideUiDiv = document.createElement('div');

            try {
                courseData = await ajax.call([{
                    methodname: 'get_course_information_for_about',
                    args: {
                        'courseid': courseId
                    }
                }])[0];
            } catch (e) {
                console.log(e);
                return false;
            }

            courseData.course.teacher = courseTeacher;
            courseData.course.fullname = courseData.course.fullname.trim();
            courseData.imageSrc = courseImage.currentSrc;

            aboutCourse = createCourseAboutWindow(courseData);

            courseAboutWrapper.className = 'course-about-wrapper';
            courseAboutWrapper.innerHTML = aboutCourse;

            hideUiDiv.className = 'hide-ui';

            document.body.prepend(hideUiDiv);
            document.body.prepend(courseAboutWrapper);
            registerEventListeners();
        };

        const createCourseAboutWindow = (courseData) => {
            const buttonData = 
            `<div class="course-about-participate">
                <button>
                    Записаться
                </button>
            </div>`;

            let teacherData = '';
            let modulesData = '';
            
            if (courseData.course.teacher) {
                const teacher = courseData.course.teacher.split('Преподаватель:')[1].trim();

                teacherData = 
                `
                <div class="course-about-teacher bold-line">
                    Преподаватель: <span class="teacher-name" title="${teacher}">${teacher}</span>
                </div>
                `;
            }

            if(courseData.modules?.length) {
                courseData.modules.forEach((module, index) => {
                    modulesData +=
                    `
                    <div class="course-about-course-content-element ${courseData.modules.length === (index + 1) ? 'key-element' : ''}">
                        <div class="course-content-element-line">
                            <div class="course-content-element-indicator"></div>
                            <div class="course-content-element-name">
                                ${module.name}
                            </div>
                        </div>
                        <div class="course-content-element-description">
                            ${module.summary}
                        </div>
                    </div>
                    `;
                });
            }
            
            const returnData = 
            `
            <div class="course-about">
                <div class="course-about-image">
                    <img src="${courseData.imageSrc}">
                </div>

                <div class="course-about-body">
                    <div class="course-about-coursename bold-line">
                        ${courseData.course.fullname}
                    </div>

                    ${teacherData}

                    <div class="course-about-description-line bold-line">
                        Описание курса
                    </div>

                    <div class="course-about-description">
                        ${courseData.course.summary}
                    </div>

                    <input type="checkbox" id="read-about" value="">
                    <label for="read-about">
                        <div class="course-about-course-content bold-line">
                            <div class="label-name">
                            Ход курса
                            </div>
                            <div class="course-about-course-marker">
                                <svg width="20" height="10" viewBox="0 0 20 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path
                                    d="M10 10C9.72808 10.0003 9.45922 9.95412 9.21136 9.86463C8.96349 9.77515 8.74233 9.64438 8.56263 9.48106L0.406423 2.06294C0.267728 1.94252 0.160514 1.80112 0.0910807 1.64706C0.0216475 1.493 -0.00860305 1.32938 0.00210673 1.16583C0.0128184 1.00228 0.0642749 0.842089 0.153451 0.694681C0.242627 0.547271 0.367723 0.415616 0.521388 0.307451C0.675054 0.199287 0.854188 0.116798 1.04826 0.0648305C1.24233 0.0128638 1.44743 -0.00753239 1.65149 0.00484154C1.85555 0.0172164 2.05446 0.0621107 2.23654 0.136887C2.41861 0.211664 2.58016 0.314814 2.71171 0.440274L9.84371 6.92697C9.86322 6.94478 9.88725 6.95905 9.9142 6.96882C9.94116 6.9786 9.97041 6.98366 10 6.98366C10.0296 6.98366 10.0588 6.9786 10.0858 6.96882C10.1128 6.95905 10.1368 6.94478 10.1563 6.92697L17.2883 0.438944C17.4198 0.313484 17.5814 0.210336 17.7635 0.135559C17.9455 0.0607823 18.1445 0.0158871 18.3485 0.00351323C18.5526 -0.00886164 18.7577 0.0115337 18.9517 0.0635003C19.1458 0.115468 19.3249 0.197959 19.4786 0.306123C19.6323 0.414287 19.7574 0.545941 19.8466 0.693351C19.9357 0.840759 19.9872 1.00095 19.9979 1.1645C20.0086 1.32805 19.9784 1.49167 19.9089 1.64573C19.8395 1.79979 19.7323 1.94119 19.5936 2.06162L11.4399 9.4784C11.2598 9.64212 11.0384 9.77331 10.7901 9.86324C10.5418 9.95317 10.2725 9.99979 10 10Z"
                                    fill="#C0C7BA" />
                                </svg>
                            </div>
                        </div>
                    </label>

                    <div class="course-about-course-content-list">
                        ${modulesData}
                    </div>

                    ${courseData.isEnrollable ? buttonData : ""}
                    
                </div>
            </div>
            `;

            return returnData;
        };

        $(document).ready(function () {
            const buttonQuery = '.course-promo .course-promo-participate button';
            const buttons = document.querySelectorAll(buttonQuery);

            buttons.forEach(btn => {
                btn.addEventListener('click', showCourseAbout);
            });
        });
    }
);
