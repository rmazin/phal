export PHING_HOME=${TRAVIS_BUILD_DIR}/build-libs/phing-2.4.9
export PHP_CLASSPATH=${PHP_CLASSPATH}:${PHING_HOME}/classes
export BUILD_NUMBER=${TRAVIS_BUILD_NUMBER}
export CHANGELIST=${TRAVIS_COMMIT}
export BRANCH=${TRAVIS_BRANCH}

${PHING_HOME}/bin/phing -logger phing.listener.DefaultLogger
