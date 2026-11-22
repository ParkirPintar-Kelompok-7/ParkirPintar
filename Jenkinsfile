
pipeline {
    agent any

    options {
        skipDefaultCheckout(true)
        disableConcurrentBuilds()
    }

    environment {
        IMAGE_REPO = 'fdhil17/parkirpintar'
    }

    stages {

        // STAGE 1: CHECKOUT
        stage('Checkout') {
            steps {
                checkout scm
                bat 'git rev-parse --short HEAD'
            }
        }

        // STAGE 2: AUTOMATED TEST
        stage('Test') {
            steps {
                bat 'docker build -f ci/Dockerfile.test -t parkirpintar-test:local ci'

                bat 'if not exist test-results mkdir test-results'
                bat 'if exist test-results\\junit.xml del /Q test-results\\junit.xml'

                script {
                    def container = "parkirpintar-test-${env.BUILD_NUMBER}"

                    bat """
                        docker create --name ${container} -w /app -e APP_ENV=testing -e APP_KEY=base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA= -e COMPOSER_ALLOW_SUPERUSER=1 parkirpintar-test:local sh -ec "composer install --no-interaction --prefer-dist --no-progress; vendor/bin/phpunit --log-junit /tmp/junit.xml"
                    """

                    try {
                        bat 'git archive --format=tar -o ci-source.tar HEAD'

                        bat "docker cp - ${container}:/app < ci-source.tar"

                        def startCode = bat(
                            returnStatus: true,
                            script: "docker start -a ${container}"
                        )

                        def reportCode = bat(
                            returnStatus: true,
                            script: "docker cp ${container}:/tmp/junit.xml test-results\\junit.xml"
                        )

                        def exitCode = bat(
                            returnStdout: true,
                            script: "@docker inspect -f \"{{.State.ExitCode}}\" ${container}"
                        ).trim()

                        if (reportCode != 0) {
                            error('Gagal mengambil laporan JUnit XML')
                        }

                        if (startCode != 0 || exitCode != '0') {
                            error('Automated test PHPUnit gagal')
                        }
                    } finally {
                        bat(
                            returnStatus: true,
                            script: "docker rm -f ${container}"
                        )

                        bat(
                            returnStatus: true,
                            script: 'if exist ci-source.tar del /Q ci-source.tar'
                        )
                    }
                }
            }
        }

        // STAGE 3: BUILD DOCKER IMAGE
        stage('Build Image') {
            steps {
                echo 'Membangun Docker image ParkirPintar...'

                bat 'docker compose version'

                bat '''
@echo off
docker build -t %IMAGE_REPO%:%BUILD_NUMBER% -t %IMAGE_REPO%:latest .
if errorlevel 1 exit /b 1
'''

                echo 'Docker image berhasil dibuat'
            }
        }

        // STAGE 4: PUSH IMAGE KE DOCKER HUB
        stage('Push Image') {
            steps {
                echo 'Mengunggah image ParkirPintar ke Docker Hub...'

                withCredentials([
                    usernamePassword(
                        credentialsId: 'dockerhub-parkirpintar',
                        usernameVariable: 'DOCKER_USER',
                        passwordVariable: 'DOCKER_TOKEN'
                    )
                ]) {
                    script {
                        try {
                            bat '''
@echo off
set "DOCKER_CONFIG=%TEMP%\\pp-docker-auth-%BUILD_NUMBER%"

if not exist "%DOCKER_CONFIG%" mkdir "%DOCKER_CONFIG%"

powershell -NoProfile -Command "[Console]::Out.Write($env:DOCKER_TOKEN)" | docker login -u "%DOCKER_USER%" --password-stdin
if errorlevel 1 exit /b 1

docker push %IMAGE_REPO%:%BUILD_NUMBER%
if errorlevel 1 exit /b 1

docker push %IMAGE_REPO%:latest
if errorlevel 1 exit /b 1
'''
                        } finally {
                            bat(
                                returnStatus: true,
                                script: '''
@echo off
set "DOCKER_CONFIG=%TEMP%\\pp-docker-auth-%BUILD_NUMBER%"

if exist "%DOCKER_CONFIG%" (
    docker logout
    rmdir /s /q "%DOCKER_CONFIG%"
)
'''
                            )
                        }
                    }
                }

                echo 'Docker image berhasil di-push'
            }
        }

        // STAGE 5: DEPLOY DOCKER COMPOSE
        stage('Deploy') {
            steps {
                echo 'Melakukan deployment ParkirPintar...'

                withCredentials([
                    file(
                        credentialsId: 'parkirpintar-env-file',
                        variable: 'DEPLOY_ENV'
                    )
                ]) {
                    bat '''
@echo off
set "IMAGE_NAME=%IMAGE_REPO%:%BUILD_NUMBER%"

docker compose -p parkirpintar --env-file "%DEPLOY_ENV%" -f compose.deploy.yaml pull app
if errorlevel 1 exit /b 1

docker compose -p parkirpintar --env-file "%DEPLOY_ENV%" -f compose.deploy.yaml up -d --no-build
if errorlevel 1 exit /b 1

docker compose -p parkirpintar --env-file "%DEPLOY_ENV%" -f compose.deploy.yaml exec -T app php artisan migrate --force
if errorlevel 1 exit /b 1

docker compose -p parkirpintar --env-file "%DEPLOY_ENV%" -f compose.deploy.yaml ps
if errorlevel 1 exit /b 1
'''
                }

                echo 'Deployment Docker Compose selesai'
            }
        }

        // STAGE 6: SMOKE TEST
        stage('Smoke Test') {
            steps {
                echo 'Memeriksa endpoint /health ...'

                retry(12) {
                    sleep(time: 5, unit: 'SECONDS')

                    bat '''
@echo off
curl.exe --fail --silent --show-error http://127.0.0.1:8000/health
if errorlevel 1 exit /b 1
'''
                }

                echo 'Smoke Test berhasil: HTTP 200'
            }
        }
    }

    // POST ACTIONS
    post {
        always {
            script {
                if (fileExists('test-results/junit.xml')) {
                    junit(
                        testResults: 'test-results/junit.xml',
                        allowEmptyResults: false,
                        skipPublishingChecks: true
                    )
                } else {
                    echo 'Laporan JUnit belum tersedia'
                }
            }
        }

        success {
            echo 'SUCCESS: Seluruh pipeline CI/CD ParkirPintar berhasil'
        }

        failure {
            echo 'FAILED: Pipeline CI/CD ParkirPintar gagal'
        }
    }
}
