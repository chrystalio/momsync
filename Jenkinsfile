pipeline {
    agent any

    environment {
        APP_NAME    = 'momsync'
        IMAGE_NAME  = "${env.DOCKER_HUB_USER}/${APP_NAME}"
        TARGET_NODE = "${env.DEPLOY_TARGET_IP}"
        SSH_CREDS   = 'jenkins-deploy-ssh'
    }

    stages {
        stage('Build') {
            steps {
                sh 'docker build -t ${IMAGE_NAME}:latest -t ${IMAGE_NAME}:${BUILD_NUMBER} .'
            }
        }

        stage('Push') {
            steps {
                sh 'docker push ${IMAGE_NAME}:latest'
                sh 'docker push ${IMAGE_NAME}:${BUILD_NUMBER}'
            }
        }

        stage('Deploy') {
            steps {
                sshagent([SSH_CREDS]) {
                    sh """
                    ssh -o StrictHostKeyChecking=no jenkins-deploy@${TARGET_NODE} '
                        docker pull ${IMAGE_NAME}:latest &&

                        # Stop + remove the previous container (if any)
                        (docker stop ${APP_NAME} 2>/dev/null || true) &&
                        (docker rm   ${APP_NAME} 2>/dev/null || true) &&

                        # Start the new container
                        docker run -d --name ${APP_NAME} \\
                            --restart unless-stopped \\
                            -p 3080:8080 \\
                            -p 127.0.0.1:3081:8081 \\
                            --env-file /home/jenkins-deploy/configs/${APP_NAME}/.env \\
                            -v /data/momsync/storage:/var/www/html/storage \\
                            ${IMAGE_NAME}:latest &&

                        # Prune old images
                        docker image prune -f
                    '
                    """
                }
            }
        }
    }

    post {
        success {
            echo "Deployed ${APP_NAME} build #${BUILD_NUMBER} to ${TARGET_NODE}"
        }
        failure {
            echo "Deploy failed for ${APP_NAME} build #${BUILD_NUMBER}"
        }
    }
}