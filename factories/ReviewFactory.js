
angular.module('ngReviewApp')
	.factory('ReviewFactory',ReviewFactory);
	ReviewFactory.$inject=['$http','$q','$firebase'];

	function ReviewFactory($http,$q,$firebase){
		var reviews=[];
		var courses=[];
		var ref = new Firebase("https://dazzling-fire-6822.firebaseio.com/");     
		

		this.getReviews=function getReviews(courseID){
			var deferred=$q.defer();
			var request = $http({
                method: 'POST',
                url: "api/v1/reviews/list",
                data: {
                	courseID: courseID
                }
                //headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            /* Check whether the HTTP Request is Successfull or not. */
            request.success(function (data) {
               console.log(data);
               deferred.resolve(data);
               // $scope.message = "From PHP file : "+data;
            });
			//vm.reviews=ReviewFactory.getReviews(courseID);
			return deferred.promise;
		}

		
		this.addReview=function addReview(uid,courseID,reviewTitle,bookTitle,reviewBody){
			var deferred=$q.defer();
			var request = $http({
                method: 'POST',
                url: "api/v1/reviews/add",
                data: {
                	uid: uid,
                	courseID: courseID,
                	reviewTitle: reviewTitle,
                	bookTitle: bookTitle,
                	reviewBody: reviewBody
                }
                //headers: { 'Content-Type': 'application/x-www-form-urlencoded' }
            });
            /* Check whether the HTTP Request is Successfull or not. */
            request.success(function (data) {
            	message='Review Added Successfully';
               console.log("Data Added to the database successfully");
               console.log(data);
               deferred.resolve(data);
               // $scope.message = "From PHP file : "+data;
            });
			//vm.reviews=ReviewFactory.getReviews(courseID);
			console.log("Review has been added");
			return deferred.promise;
		}
		this.getCourses=function getCourses(){
			courses=[
						{courseID:"IT467",courseName:"Human Factors"},
						{courseID:"IT478",courseName:"Advanced Database"},
						{courseID:"IT353",courseName:"Web Technologies"},
						{courseID:"IT354",courseName:"Advanced Web Technologies"},
						{courseID:"IT432",courseName:"System Analysis"},
						{courseID:"IT377",courseName:"Telecoms"}
					];
			return courses;
		};
		this.getCoursesFirebase=function getCoursesFirebase(){
				var deferred=$q.defer();
				var ref = new Firebase("https://dazzling-fire-6822.firebaseio.com/courses"); 
				var snapshotval=[];    
				// Attach an asynchronous callback to read the data at our posts reference
				ref.on("value", function(snapshot) {
				  //console.log(snapshot.val());
				  snapshotval=snapshot.val();
				  console.log(snapshotval);
				  deferred.resolve(snapshotval);
				}, function (errorObject) {
				  console.log("The read failed: " + errorObject.code);
				  deferred.reject("The read failed: " + errorObject.code);
				});		
				return deferred.promise;
		};

		this.setCourses=function setCourses(){
			console.log("here set courses");
			coursesRef=ref.child("courses");
			coursesRef.set({
			  IT467: {
			    courseID: "IT467",
			    courseName: "Human Factors"
			  },
			  IT478: {
			    courseID: "IT478",
			    courseName: "Advanced Database"
			  },
			  IT353: {
			    courseID: "IT353",
			    courseName: "Web Technologies"
			  },
			  IT354: {
			    courseID: "IT354",
			    courseName: "Advanced Web Technologies"
			  },
			  IT432: {
			    courseID: "IT432",
			    courseName: "System Analysis"
			  },
			  IT377: {
			    courseID: "IT377",
			    courseName: "Telecoms"
			  }
			});		
		};
		return this;
	}
